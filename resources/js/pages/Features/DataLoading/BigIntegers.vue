<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    preserve: boolean;
    orderId: bigint | number;
    expectedOrderId: string;
    customerId: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Data Loading' },
    { title: 'Big Integers' },
];

const page = usePage();

const isExact = computed(() => String(props.orderId) === props.expectedOrderId);

const receivedOrderId = computed(
    () => page.flash.receivedOrderId as string | undefined,
);

function submitOrderId() {
    router.post(
        '/features/data-loading/big-integers',
        { id: props.orderId },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Big Integers" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Big Integers"
                docs="advanced/big-integers"
                controller="app/Http/Controllers/Feature/DataLoadingController.php#L234"
            >
                JavaScript rounds integers outside its safe range while parsing
                JSON. With
                <code class="text-xs">preserveBigIntegers()</code> they arrive
                as native <code class="text-xs">BigInt</code> values instead.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <FeatureCard title="Server Setting" badge="v3.8">
                    <template #description>
                        Toggle big integer support for this response and watch
                        the order ID change.
                    </template>
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <Button
                                as-child
                                size="sm"
                                :variant="preserve ? 'default' : 'outline'"
                            >
                                <Link
                                    href="/features/data-loading/big-integers?preserve=1"
                                    preserve-scroll
                                    >Enabled</Link
                                >
                            </Button>
                            <Button
                                as-child
                                size="sm"
                                :variant="preserve ? 'outline' : 'default'"
                            >
                                <Link
                                    href="/features/data-loading/big-integers?preserve=0"
                                    preserve-scroll
                                    >Disabled</Link
                                >
                            </Button>
                        </div>

                        <CodeBlock
                            title="Controller:"
                            code="
                                return Inertia::render('Orders/Show', [
                                    'orderId' => 900719925474099988,
                                ])->preserveBigIntegers();
                            "
                        />

                        <p class="text-xs text-muted-foreground">
                            You may also enable it for every response with the
                            <code>preserve_big_integers</code> option in
                            <code>config/inertia.php</code>.
                        </p>
                    </div>
                </FeatureCard>

                <FeatureCard
                    info-card
                    title="Received Props"
                    description="What the page component receives from the server."
                >
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >Order ID</span
                                >
                                <Badge
                                    :variant="
                                        isExact ? 'secondary' : 'destructive'
                                    "
                                    data-test="order-id-status"
                                >
                                    {{ isExact ? 'Exact' : 'Rounded' }}
                                </Badge>
                            </div>
                            <p class="font-mono text-sm" data-test="order-id">
                                {{ orderId }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                typeof:
                                <code data-test="order-id-type">{{
                                    typeof orderId
                                }}</code>
                                · sent as {{ expectedOrderId }}
                            </p>
                        </div>

                        <div class="space-y-1">
                            <span class="text-sm font-medium">Customer ID</span>
                            <p class="font-mono text-sm">{{ customerId }}</p>
                            <p class="text-xs text-muted-foreground">
                                typeof:
                                <code data-test="customer-id-type">{{
                                    typeof customerId
                                }}</code>
                                · integers within the safe range stay regular
                                numbers
                            </p>
                        </div>
                    </div>
                </FeatureCard>

                <FeatureCard
                    class="lg:col-span-2"
                    title="Submitting Big Integers"
                >
                    <template #description>
                        A <code class="text-xs">BigInt</code> submitted through
                        the router is sent as its digits, so the controller
                        receives the exact value.
                    </template>
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-3">
                            <Button size="sm" @click="submitOrderId">
                                Submit Order ID
                            </Button>
                            <span
                                v-if="receivedOrderId"
                                class="text-sm"
                                data-test="received-order-id"
                            >
                                Server received
                                <code>{{ receivedOrderId }}</code>
                                <Badge
                                    class="ml-2"
                                    :variant="
                                        receivedOrderId === expectedOrderId
                                            ? 'secondary'
                                            : 'destructive'
                                    "
                                >
                                    {{
                                        receivedOrderId === expectedOrderId
                                            ? 'Exact'
                                            : 'Rounded'
                                    }}
                                </Badge>
                            </span>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <CodeBlock
                                title="Page component:"
                                code="
                                    router.post('/orders', {
                                      id: 900719925474099988n,
                                    })
                                "
                                data-test="submit-client-code"
                            />
                            <CodeBlock
                                title="Controller:"
                                code="
                                    $request->validate(['id' => ['required', 'integer']]);

                                    $request->integer('id'); // 900719925474099988
                                "
                            />
                        </div>
                    </div>
                </FeatureCard>
            </div>
        </div>
    </AppLayout>
</template>
