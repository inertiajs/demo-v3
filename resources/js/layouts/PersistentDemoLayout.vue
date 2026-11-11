<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Timer, Hash } from '@lucide/vue';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';

const breadcrumbs = [
    { title: 'Layouts & Head' },
    { title: 'Persistent Layouts' },
];

const elapsed = ref(0);
const counter = ref(0);
let interval: ReturnType<typeof setInterval>;

onMounted(() => {
    interval = setInterval(() => elapsed.value++, 1000);
});

onUnmounted(() => {
    clearInterval(interval);
});

const formattedTime = computed(() => {
    const min = Math.floor(elapsed.value / 60);
    const sec = elapsed.value % 60;
    return `${min}:${sec.toString().padStart(2, '0')}`;
});

const page = usePage();
const currentPath = computed(() => page.url);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Persistent Layouts"
                docs="the-basics/layouts#persistent-layouts"
                controller="app/Http/Controllers/Feature/LayoutController.php#L11"
            >
                Layout state survives page navigations. The stopwatch and
                counter below live in the layout and persist as you switch
                pages.
            </FeatureHeader>

            <div
                class="flex flex-col gap-4 rounded-xl border border-black/10 bg-card p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-white/10"
            >
                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <Timer class="size-4 text-muted-foreground" />
                        <span class="text-sm font-medium">Stopwatch</span>
                        <Badge variant="secondary" class="font-mono text-base">
                            {{ formattedTime }}
                        </Badge>
                    </div>

                    <div class="flex items-center gap-2">
                        <Hash class="size-4 text-muted-foreground" />
                        <span class="text-sm font-medium">Counter</span>
                        <Button
                            variant="outline"
                            size="sm"
                            class="size-7 p-0"
                            @click="counter--"
                        >
                            -
                        </Button>
                        <Badge variant="secondary" class="font-mono text-base">
                            {{ counter }}
                        </Badge>
                        <Button
                            variant="outline"
                            size="sm"
                            class="size-7 p-0"
                            @click="counter++"
                        >
                            +
                        </Button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        href="/features/layouts/persistent-layouts"
                        class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            currentPath ===
                            '/features/layouts/persistent-layouts'
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground hover:bg-accent hover:text-foreground'
                        "
                    >
                        Page 1
                    </Link>
                    <Link
                        href="/features/layouts/persistent-layouts/page-2"
                        class="inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium transition-colors"
                        :class="
                            currentPath ===
                            '/features/layouts/persistent-layouts/page-2'
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground hover:bg-accent hover:text-foreground'
                        "
                    >
                        Page 2
                    </Link>
                    <Link
                        href="/contacts"
                        class="inline-flex items-center rounded-md px-3 py-1.5 text-sm text-muted-foreground hover:text-foreground"
                    >
                        Leave layout &rarr;
                    </Link>
                </div>
            </div>

            <slot />
        </div>
    </AppLayout>
</template>
