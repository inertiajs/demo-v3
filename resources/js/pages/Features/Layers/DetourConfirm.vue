<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import layers from '@/wayfinder/routes/features/layers';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Layers' },
    { title: 'Detours' },
    { title: 'Confirm' },
];
</script>

<template>
    <Head title="Confirm" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <Card class="max-w-xl">
                <CardHeader>
                    <CardTitle>Confirm to continue</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm text-muted-foreground">
                        This is an ordinary page, not a layer. When you are sent
                        here on the way to a layer, the client holds on to the
                        page you asked for it from and opens the layer back over
                        it.
                    </p>
                    <Button
                        size="sm"
                        @click="router.post(layers.detour.complete().url)"
                    >
                        Confirm and continue
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
