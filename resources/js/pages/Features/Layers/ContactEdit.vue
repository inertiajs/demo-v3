<script setup lang="ts">
import { useForm, useLayer } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import layers from '@/wayfinder/routes/features/layers';

const props = defineProps<{
    contact: {
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string | null;
        organization?: { id: number; name: string };
    };
    organizations: { id: number; name: string }[];
}>();

const layer = useLayer();

const form = useForm({
    first_name: props.contact.first_name,
    last_name: props.contact.last_name,
    email: props.contact.email ?? '',
    phone: props.contact.phone ?? '',
    organization_id: props.contact.organization?.id ?? null,
});

function submit() {
    form.put(layers.dialogs.update(props.contact.id).url);
}
</script>

<template>
    <form class="space-y-4" @submit.prevent="submit">
        <p class="text-xs text-muted-foreground">
            Clear a name and save. The validation errors come back to this
            layer, not to the page underneath — a request made inside a layer
            belongs to it.
        </p>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-2">
                <Label for="first_name">First name</Label>
                <Input id="first_name" v-model="form.first_name" />
                <InputError :message="form.errors.first_name" />
            </div>
            <div class="space-y-2">
                <Label for="last_name">Last name</Label>
                <Input id="last_name" v-model="form.last_name" />
                <InputError :message="form.errors.last_name" />
            </div>
        </div>

        <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input id="email" v-model="form.email" type="email" />
            <InputError :message="form.errors.email" />
        </div>

        <div class="space-y-2">
            <Label for="phone">Phone</Label>
            <Input id="phone" v-model="form.phone" />
            <InputError :message="form.errors.phone" />
        </div>

        <div class="space-y-2">
            <Label for="organization_id">Organization</Label>
            <select
                id="organization_id"
                v-model="form.organization_id"
                class="h-9 w-full rounded-md border bg-transparent px-3 text-sm"
            >
                <option :value="null">No organization</option>
                <option
                    v-for="org in organizations"
                    :key="org.id"
                    :value="org.id"
                >
                    {{ org.name }}
                </option>
            </select>
            <InputError :message="form.errors.organization_id" />
        </div>

        <div class="flex items-center justify-end gap-2 border-t pt-4">
            <Button
                type="button"
                variant="ghost"
                size="sm"
                @click="layer.close()"
                >Cancel</Button
            >
            <Button type="submit" size="sm" :disabled="form.processing">
                Save
            </Button>
        </div>

        <p class="text-xs text-muted-foreground">
            A successful save answers with
            <code>Inertia::close()</code>, which closes this layer and refreshes
            the page beneath it instead of navigating anywhere.
        </p>
    </form>
</template>
