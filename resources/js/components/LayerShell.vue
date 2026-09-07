<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Modal from '@/components/Modal.vue';
import Slideover from '@/components/Slideover.vue';
import { Badge } from '@/components/ui/badge';
import type { LayerDialog } from '@/types';

const props = defineProps<{
    open: boolean;
    index: number;
    isTop: boolean;
    type: 'routed' | 'local';
    close: () => void;
    done: () => void;
}>();

const page = usePage();

const dialog = computed<LayerDialog>(
    () => (page.props.dialog as LayerDialog | undefined) ?? {},
);
</script>

<template>
    <component
        :is="dialog.variant === 'slideover' ? Slideover : Modal"
        v-bind="props"
        :title="dialog.title"
        :description="dialog.description"
        :size="dialog.size ?? 'md'"
    >
        <template #title>
            {{ dialog.title }}
            <Badge
                v-if="type === 'local'"
                as="span"
                variant="outline"
                class="text-[10px]"
                >local</Badge
            >
        </template>

        <slot />
    </component>
</template>
