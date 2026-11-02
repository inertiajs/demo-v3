<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { RotateCcw } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/wayfinder/routes';

const page = usePage();
const wasReset = ref(false);

// The reset time is a live prop, so it changes on every open page the moment
// the daily reset is broadcast
watch(
    () => page.props.demoResetAt,
    (resetAt, previousResetAt) => {
        if (previousResetAt && resetAt !== previousResetAt) {
            wasReset.value = true;
        }
    },
);

// Detail pages point at records the reset deleted, so those land on the dashboard
function reload() {
    wasReset.value = false;
    router.reload({
        onHttpException: () => {
            router.visit(dashboard());

            return false;
        },
    });
}
</script>

<template>
    <div v-if="wasReset" class="px-4 pt-4" data-test="demo-reset-banner">
        <Alert>
            <RotateCcw class="size-4" />
            <AlertTitle>The demo data was just reset</AlertTitle>
            <AlertDescription class="flex flex-wrap items-center gap-3">
                Contacts, organizations and notes were replaced with fresh ones.
                <Button size="sm" @click="reload">Reload</Button>
            </AlertDescription>
        </Alert>
    </div>
</template>
