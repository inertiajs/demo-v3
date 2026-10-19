<script setup lang="ts">
import { useForm, useLayer } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';

const props = defineProps<{ contact: string; action: string }>();

const layer = useLayer();
const form = useForm({ body: '' });
</script>

<template>
    <div class="space-y-4">
        <p class="text-sm text-muted-foreground">
            A note about {{ contact }}. This layer was composed on the client —
            the server was never asked for it, and the address never moved.
        </p>

        <form class="space-y-3" @submit.prevent="form.post(props.action)">
            <div class="space-y-2">
                <Label for="body">Note</Label>
                <textarea
                    id="body"
                    v-model="form.body"
                    rows="4"
                    class="w-full rounded-md border bg-transparent px-3 py-2 text-sm"
                    placeholder="Had a great call about…"
                ></textarea>
                <InputError :message="form.errors.body" />
            </div>

            <div class="flex justify-end gap-2">
                <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="layer.close()"
                    >Cancel</Button
                >
                <Button type="submit" size="sm" :disabled="form.processing"
                    >Save note</Button
                >
            </div>
        </form>

        <p class="text-xs text-muted-foreground">
            The post is made from in here, so its validation errors land in here
            too. On success the response is the layer beneath coming back, which
            rewrites that layer and closes this one on the way.
        </p>
    </div>
</template>
