<script setup lang="ts">
import { Form, Head, InfiniteScroll, Link } from '@inertiajs/vue3';
import { Building2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import contactRoutes from '@/wayfinder/routes/contacts';
import organizationRoutes from '@/wayfinder/routes/organizations';
import type { App, Inertia } from '@/wayfinder/types';

type CursorPaginated<T> = {
    data: T[];
    next_cursor: string | null;
    next_page_url: string | null;
    prev_cursor: string | null;
    prev_page_url: string | null;
};

const props = defineProps<
    Omit<Inertia.Pages.Organizations.Show, 'contacts'> & {
        contacts: CursorPaginated<App.Models.Contact>;
    }
>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'CRM' },
    { title: 'Organizations', href: organizationRoutes.index().url },
    { title: props.organization.name },
];
</script>

<template>
    <Head :title="organization.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <!-- Organization Header with Inline Edit -->
            <Card>
                <CardHeader>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10"
                        >
                            <Building2 class="size-6 shrink-0 text-primary" />
                        </div>
                        <div>
                            <CardTitle class="text-2xl tracking-tight">{{
                                organization.name
                            }}</CardTitle>
                            <Badge variant="outline" class="mt-1">
                                {{ organization.contacts_count }}
                                {{
                                    organization.contacts_count === 1
                                        ? 'contact'
                                        : 'contacts'
                                }}
                            </Badge>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Form
                        :action="organizationRoutes.update(organization.id).url"
                        method="put"
                        set-defaults-on-success
                        #default="{
                            errors,
                            processing,
                            isDirty,
                            recentlySuccessful,
                        }"
                    >
                        <div class="flex max-w-md items-end gap-3">
                            <div class="flex-1 space-y-2">
                                <label for="name" class="text-sm font-medium"
                                    >Organization Name</label
                                >
                                <Input
                                    id="name"
                                    name="name"
                                    :default-value="organization.name"
                                />
                                <p
                                    v-if="errors.name"
                                    class="text-sm text-red-600"
                                >
                                    {{ errors.name }}
                                </p>
                            </div>
                            <Button
                                type="submit"
                                :disabled="processing || !isDirty"
                            >
                                {{ processing ? 'Saving...' : 'Update' }}
                            </Button>
                        </div>
                        <p
                            v-if="recentlySuccessful"
                            class="mt-2 text-sm text-green-600"
                        >
                            Saved!
                        </p>
                    </Form>
                </CardContent>
            </Card>

            <!-- Members -->
            <Card>
                <CardHeader>
                    <CardTitle>Members</CardTitle>
                </CardHeader>
                <CardContent>
                    <InfiniteScroll
                        data="contacts"
                        :buffer="300"
                        preserve-url
                        class="space-y-2"
                    >
                        <Link
                            v-for="contact in props.contacts.data"
                            :key="contact.id"
                            :href="contactRoutes.show(contact.id).url"
                            class="flex items-center gap-3 rounded-lg bg-muted/30 p-3 hover:bg-muted/50"
                        >
                            <div
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-medium text-primary"
                            >
                                {{ contact.first_name[0]
                                }}{{ contact.last_name[0] }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="text-sm font-medium"
                                    >{{ contact.first_name }}
                                    {{ contact.last_name }}</span
                                >
                                <div
                                    v-if="contact.email"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ contact.email }}
                                </div>
                            </div>
                        </Link>

                        <template #loading>
                            <div class="flex justify-center py-4">
                                <div class="text-sm text-muted-foreground">
                                    Loading more...
                                </div>
                            </div>
                        </template>
                    </InfiniteScroll>

                    <div
                        v-if="props.contacts.data.length === 0"
                        class="py-4 text-center text-sm text-muted-foreground"
                    >
                        No contacts in this organization.
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
