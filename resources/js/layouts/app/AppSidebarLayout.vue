<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import DemoResetBanner from '@/components/DemoResetBanner.vue';
import FlashToast from '@/components/FlashToast.vue';
import { useFlashToast } from '@/composables/useFlashToast';
import type { BreadcrumbItem } from '@/types';

useFlashToast();

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <DemoResetBanner />
            <slot />
        </AppContent>
    </AppShell>
    <FlashToast />
</template>
