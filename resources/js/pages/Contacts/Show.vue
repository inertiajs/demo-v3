<script setup lang="ts">
import { Deferred, Form, Head, Link, router } from '@inertiajs/vue3';
import { Building2, Heart, Mail, Pencil, Phone, Trash2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import contacts from '@/wayfinder/routes/contacts';
import type { App, Inertia } from '@/wayfinder/types';

const props = defineProps<
    Omit<Inertia.Pages.Contacts.Show, 'notes'> & {
        notes?: App.Models.Note[];
    }
>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM' },
    { title: 'Contacts', href: contacts.index().url },
    { title: `${props.contact.first_name} ${props.contact.last_name}` },
];

function toggleFavorite() {
    router.post(
        contacts.favorite(props.contact.id).url,
        {},
        {
            preserveScroll: true,
            optimistic: (currentProps: Record<string, unknown>) => {
                const contact = currentProps.contact as App.Models.Contact;
                return {
                    contact: { ...contact, is_favorite: !contact.is_favorite },
                };
            },
        },
    );
}

function deleteContact() {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(contacts.destroy(props.contact.id).url);
    }
}
</script>

<template>
    <Head :title="`${contact.first_name} ${contact.last_name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Contact Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <div
                        class="flex size-16 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xl font-semibold text-primary"
                    >
                        {{ contact.first_name[0] }}{{ contact.last_name[0] }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-semibold tracking-tight">
                                {{ contact.first_name }} {{ contact.last_name }}
                            </h1>
                            <button
                                type="button"
                                @click="toggleFavorite"
                                class="focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ring"
                            >
                                <Heart
                                    class="size-5 shrink-0"
                                    :class="
                                        contact.is_favorite
                                            ? 'fill-red-500 text-red-500'
                                            : 'text-muted-foreground hover:text-red-500'
                                    "
                                />
                            </button>
                        </div>
                        <Badge
                            v-if="contact.organization"
                            variant="secondary"
                            class="mt-1"
                        >
                            <Building2 class="size-3 shrink-0" />
                            {{ contact.organization.name }}
                        </Badge>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="contacts.edit(contact.id).url">
                            <Pencil class="size-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button
                        variant="outline"
                        class="text-destructive"
                        @click="deleteContact"
                    >
                        <Trash2 class="size-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <!-- Contact Details -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Contact Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="contact.email"
                            class="flex items-center gap-2 text-sm"
                        >
                            <Mail
                                class="size-4 shrink-0 text-muted-foreground"
                            />
                            <a
                                :href="`mailto:${contact.email}`"
                                class="text-primary hover:underline"
                                >{{ contact.email }}</a
                            >
                        </div>
                        <div
                            v-if="contact.phone"
                            class="flex items-center gap-2 text-sm"
                        >
                            <Phone
                                class="size-4 shrink-0 text-muted-foreground"
                            />
                            <span>{{ contact.phone }}</span>
                        </div>
                        <div
                            v-if="!contact.email && !contact.phone"
                            class="text-sm text-muted-foreground"
                        >
                            No contact information available.
                        </div>
                    </CardContent>
                </Card>

                <!-- Notes -->
                <Card>
                    <CardHeader>
                        <CardTitle>Notes</CardTitle>
                        <CardDescription
                            >Add and view notes for this
                            contact</CardDescription
                        >
                    </CardHeader>
                    <CardContent>
                        <!-- Add Note Form -->
                        <Form
                            :action="contacts.notes.store(contact.id).url"
                            method="post"
                            reset-on-success
                            :options="{ preserveScroll: true }"
                            class="mb-4"
                            #default="{ errors, processing }"
                        >
                            <textarea
                                name="body"
                                placeholder="Add a note..."
                                class="w-full rounded-md border bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:outline-none"
                                rows="3"
                            />
                            <div
                                v-if="errors.body"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ errors.body }}
                            </div>
                            <Button
                                type="submit"
                                size="sm"
                                class="mt-2"
                                :disabled="processing"
                            >
                                Add Note
                            </Button>
                        </Form>

                        <!-- Notes List (Deferred) -->
                        <Deferred data="notes">
                            <template #fallback>
                                <div class="space-y-3">
                                    <Skeleton class="h-16 w-full" />
                                    <Skeleton class="h-16 w-full" />
                                </div>
                            </template>
                            <div
                                v-if="notes && notes.length > 0"
                                class="space-y-3"
                            >
                                <div
                                    v-for="note in notes"
                                    :key="note.id"
                                    class="rounded-lg bg-muted/30 p-3"
                                >
                                    <div
                                        class="mb-1 flex items-center justify-between"
                                    >
                                        <span class="text-sm font-medium">{{
                                            note.user?.name
                                        }}</span>
                                        <time
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{
                                                new Date(
                                                    note.created_at!,
                                                ).toLocaleDateString()
                                            }}
                                        </time>
                                    </div>
                                    <p class="text-sm text-muted-foreground">
                                        {{ note.body }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-sm text-muted-foreground">
                                No notes yet.
                            </p>
                        </Deferred>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
