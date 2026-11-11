<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronRight } from '@lucide/vue';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import {
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { type NavGroup } from '@/types';

const props = defineProps<{
    group: NavGroup;
}>();

const { isCurrentUrl } = useCurrentUrl();

function isGroupActive(): boolean {
    return props.group.items.some((item) => isCurrentUrl(item.href));
}
</script>

<template>
    <SidebarMenuItem>
        <Collapsible :default-open="isGroupActive()" class="group/collapsible">
            <CollapsibleTrigger as-child>
                <SidebarMenuButton :tooltip="group.title">
                    <component :is="group.icon" v-if="group.icon" />
                    <span>{{ group.title }}</span>
                    <ChevronRight
                        class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                    />
                </SidebarMenuButton>
            </CollapsibleTrigger>
            <CollapsibleContent>
                <SidebarMenuSub>
                    <SidebarMenuSubItem
                        v-for="item in group.items"
                        :key="item.title"
                    >
                        <SidebarMenuSubButton
                            as-child
                            :is-active="isCurrentUrl(item.href)"
                        >
                            <Link :href="item.href" class="flex items-center">
                                <span class="truncate">{{ item.title }}</span>
                                <span
                                    v-if="item.badge"
                                    class="ml-auto shrink-0 rounded bg-indigo-500/10 px-1 py-0.5 text-[9px] leading-none font-semibold text-indigo-500"
                                    >{{ item.badge }}</span
                                >
                            </Link>
                        </SidebarMenuSubButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
            </CollapsibleContent>
        </Collapsible>
    </SidebarMenuItem>
</template>
