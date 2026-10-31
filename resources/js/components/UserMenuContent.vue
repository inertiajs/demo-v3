<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Monitor, Moon, Sun } from '@lucide/vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { useAppearance } from '@/composables/useAppearance';
import type { User } from '@/types';
import { logout } from '@/wayfinder/routes';

type Props = {
    user: User;
};

const { appearance, updateAppearance } = useAppearance();

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem @click="updateAppearance('light')">
            <Sun class="mr-2 h-4 w-4" />
            Light
            <span
                v-if="appearance === 'light'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
        <DropdownMenuItem @click="updateAppearance('dark')">
            <Moon class="mr-2 h-4 w-4" />
            Dark
            <span
                v-if="appearance === 'dark'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
        <DropdownMenuItem @click="updateAppearance('system')">
            <Monitor class="mr-2 h-4 w-4" />
            System
            <span
                v-if="appearance === 'system'"
                class="ml-auto text-xs text-muted-foreground"
                >&#10003;</span
            >
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full cursor-pointer"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </Link>
    </DropdownMenuItem>
</template>
