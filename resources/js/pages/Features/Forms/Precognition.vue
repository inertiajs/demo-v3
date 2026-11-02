<script setup lang="ts">
import { Form, Head, useForm } from '@inertiajs/vue3';
import { CheckCircle2, XCircle } from '@lucide/vue';
import { ref } from 'vue';
import CodeBlock from '@/components/CodeBlock.vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { storeAccount } from '@/wayfinder/App/Http/Controllers/Feature/FormController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Forms' },
    { title: 'Precognition' },
];

const activeTab = ref<'useForm' | 'formComponent'>('useForm');

const form = useForm('post', storeAccount.url(), {
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

form.setValidationTimeout(500);
</script>

<template>
    <Head title="Precognition" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="Precognition"
                docs="the-basics/forms#precognition"
                controller="app/Http/Controllers/Feature/FormController.php#L71"
            >
                Real-time server-side validation on field change. No page
                submission needed.
            </FeatureHeader>

            <!-- Approach Switcher -->
            <div class="flex gap-2">
                <Button
                    :variant="activeTab === 'useForm' ? 'default' : 'outline'"
                    size="sm"
                    @click="activeTab = 'useForm'"
                >
                    useForm + Precognition
                </Button>
                <Button
                    :variant="
                        activeTab === 'formComponent' ? 'default' : 'outline'
                    "
                    size="sm"
                    @click="activeTab = 'formComponent'"
                >
                    Form Component + Precognition
                </Button>
            </div>

            <div
                v-if="activeTab === 'useForm'"
                class="grid gap-6 lg:grid-cols-2"
            >
                <!-- Form -->
                <FeatureCard
                    title="Create Account (useForm)"
                    description="Type in each field and tab away. Validation runs against the server in real time via precognition."
                >
                    <form class="space-y-4" @submit.prevent="form.submit()">
                        <div class="space-y-2">
                            <Label for="p-username">Username</Label>
                            <div class="relative">
                                <Input
                                    id="p-username"
                                    v-model="form.username"
                                    @change="form.validate('username')"
                                    :class="{
                                        'border-green-500':
                                            form.valid('username'),
                                        'border-red-500':
                                            form.invalid('username'),
                                    }"
                                />
                                <div
                                    class="absolute top-1/2 right-2 -translate-y-1/2"
                                >
                                    <CheckCircle2
                                        v-if="form.valid('username')"
                                        class="size-4 text-green-500"
                                    />
                                    <XCircle
                                        v-else-if="form.invalid('username')"
                                        class="size-4 text-red-500"
                                    />
                                </div>
                            </div>
                            <p
                                v-if="form.invalid('username')"
                                class="text-sm text-red-600"
                            >
                                {{ form.errors.username }}
                            </p>
                            <p
                                v-else-if="form.valid('username')"
                                class="text-sm text-green-600"
                            >
                                Username available!
                            </p>
                            <p class="text-xs text-muted-foreground">
                                3-20 characters, letters, numbers, dashes only.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="p-email">Email</Label>
                            <div class="relative">
                                <Input
                                    id="p-email"
                                    type="text"
                                    v-model="form.email"
                                    @change="form.validate('email')"
                                    :class="{
                                        'border-green-500': form.valid('email'),
                                        'border-red-500': form.invalid('email'),
                                    }"
                                />
                                <div
                                    class="absolute top-1/2 right-2 -translate-y-1/2"
                                >
                                    <CheckCircle2
                                        v-if="form.valid('email')"
                                        class="size-4 text-green-500"
                                    />
                                    <XCircle
                                        v-else-if="form.invalid('email')"
                                        class="size-4 text-red-500"
                                    />
                                </div>
                            </div>
                            <p
                                v-if="form.invalid('email')"
                                class="text-sm text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="p-password">Password</Label>
                            <Input
                                id="p-password"
                                type="password"
                                v-model="form.password"
                                @change="form.validate('password')"
                                :class="{
                                    'border-green-500': form.valid('password'),
                                    'border-red-500': form.invalid('password'),
                                }"
                            />
                            <p
                                v-if="form.invalid('password')"
                                class="text-sm text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                            <p v-else class="text-xs text-muted-foreground">
                                Minimum 8 characters.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="p-password-confirmation"
                                >Confirm Password</Label
                            >
                            <Input
                                id="p-password-confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                @change="form.validate('password_confirmation')"
                                :class="{
                                    'border-green-500': form.valid(
                                        'password_confirmation',
                                    ),
                                    'border-red-500': form.invalid(
                                        'password_confirmation',
                                    ),
                                }"
                            />
                            <p
                                v-if="form.invalid('password_confirmation')"
                                class="text-sm text-red-600"
                            >
                                {{ form.errors.password_confirmation }}
                            </p>
                        </div>

                        <div class="pt-2">
                            <Button
                                type="submit"
                                :disabled="form.processing || form.validating"
                            >
                                {{
                                    form.processing
                                        ? 'Creating...'
                                        : form.validating
                                          ? 'Validating...'
                                          : 'Create Account'
                                }}
                            </Button>
                        </div>
                    </form>
                </FeatureCard>

                <!-- State Display -->
                <div class="space-y-6">
                    <FeatureCard
                        info-card
                        title="Precognition State"
                        description="Real-time state from the precognition validation system."
                    >
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >validating</span
                                >
                                <Badge
                                    :variant="
                                        form.validating
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    >{{ form.validating }}</Badge
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >hasErrors</span
                                >
                                <Badge
                                    :variant="
                                        form.hasErrors
                                            ? 'destructive'
                                            : 'secondary'
                                    "
                                    >{{ form.hasErrors }}</Badge
                                >
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >processing</span
                                >
                                <Badge
                                    :variant="
                                        form.processing
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    >{{ form.processing }}</Badge
                                >
                            </div>
                        </div>
                    </FeatureCard>

                    <FeatureCard
                        info-card
                        title="Field Status"
                        description="Per-field touched, valid, and invalid state."
                    >
                        <div class="space-y-3">
                            <div
                                v-for="field in [
                                    'username',
                                    'email',
                                    'password',
                                    'password_confirmation',
                                ] as const"
                                :key="field"
                                class="flex items-center justify-between text-sm"
                            >
                                <span class="font-medium">{{ field }}</span>
                                <div class="flex gap-1">
                                    <Badge
                                        v-if="form.touched(field)"
                                        variant="outline"
                                        class="text-xs"
                                        >touched</Badge
                                    >
                                    <Badge
                                        v-if="form.valid(field)"
                                        variant="default"
                                        class="bg-green-600 text-xs"
                                        >valid</Badge
                                    >
                                    <Badge
                                        v-if="form.invalid(field)"
                                        variant="destructive"
                                        class="text-xs"
                                        >invalid</Badge
                                    >
                                </div>
                            </div>
                        </div>
                    </FeatureCard>

                    <FeatureCard
                        info-card
                        title="Actions"
                        description="validate() only triggers a precognitive request when the field's value differs from its default. Unchanged fields are skipped."
                    >
                        <div class="flex flex-wrap gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="form.touch('username')"
                                >touch('username')</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="form.validate('username')"
                                >validate('username')</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="form.clearErrors()"
                                >clearErrors()</Button
                            >
                            <Button
                                variant="outline"
                                size="sm"
                                @click="form.reset()"
                                >reset()</Button
                            >
                        </div>
                    </FeatureCard>
                </div>
            </div>

            <!-- Form Component Precognition Tab -->
            <div
                v-if="activeTab === 'formComponent'"
                class="grid gap-6 lg:grid-cols-2"
            >
                <FeatureCard title="Create Account (Form Component)">
                    <template #description>
                        The <code class="text-xs">&lt;Form&gt;</code> component
                        has built-in precognition support via
                        <code class="text-xs">:validation-timeout</code> and
                        slot props like <code class="text-xs">validate</code>,
                        <code class="text-xs">valid</code>,
                        <code class="text-xs">invalid</code>.
                    </template>

                    <Form
                        v-bind="storeAccount.form()"
                        :validation-timeout="500"
                        class="space-y-4"
                        #default="{
                            errors,
                            processing,
                            validating,
                            validate,
                            valid,
                            invalid,
                        }"
                    >
                        <div class="space-y-2">
                            <Label for="fc-username">Username</Label>
                            <div class="relative">
                                <Input
                                    id="fc-username"
                                    name="username"
                                    @change="validate('username')"
                                    :class="{
                                        'border-green-500': valid('username'),
                                        'border-red-500': invalid('username'),
                                    }"
                                />
                                <div
                                    class="absolute top-1/2 right-2 -translate-y-1/2"
                                >
                                    <CheckCircle2
                                        v-if="valid('username')"
                                        class="size-4 text-green-500"
                                    />
                                    <XCircle
                                        v-else-if="invalid('username')"
                                        class="size-4 text-red-500"
                                    />
                                </div>
                            </div>
                            <InputError :message="errors.username" />
                            <p
                                v-if="valid('username')"
                                class="text-sm text-green-600"
                            >
                                Username available!
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="fc-email">Email</Label>
                            <div class="relative">
                                <Input
                                    id="fc-email"
                                    type="text"
                                    name="email"
                                    @change="validate('email')"
                                    :class="{
                                        'border-green-500': valid('email'),
                                        'border-red-500': invalid('email'),
                                    }"
                                />
                                <div
                                    class="absolute top-1/2 right-2 -translate-y-1/2"
                                >
                                    <CheckCircle2
                                        v-if="valid('email')"
                                        class="size-4 text-green-500"
                                    />
                                    <XCircle
                                        v-else-if="invalid('email')"
                                        class="size-4 text-red-500"
                                    />
                                </div>
                            </div>
                            <InputError :message="errors.email" />
                        </div>

                        <div class="space-y-2">
                            <Label for="fc-password">Password</Label>
                            <Input
                                id="fc-password"
                                type="password"
                                name="password"
                                @change="validate('password')"
                                :class="{
                                    'border-green-500': valid('password'),
                                    'border-red-500': invalid('password'),
                                }"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="fc-password-confirm"
                                >Confirm Password</Label
                            >
                            <Input
                                id="fc-password-confirm"
                                type="password"
                                name="password_confirmation"
                                @change="validate('password_confirmation')"
                                :class="{
                                    'border-green-500': valid(
                                        'password_confirmation',
                                    ),
                                    'border-red-500': invalid(
                                        'password_confirmation',
                                    ),
                                }"
                            />
                            <InputError
                                :message="errors.password_confirmation"
                            />
                        </div>

                        <div class="pt-2">
                            <Button
                                type="submit"
                                :disabled="processing || validating"
                            >
                                {{
                                    processing
                                        ? 'Creating...'
                                        : validating
                                          ? 'Validating...'
                                          : 'Create Account'
                                }}
                            </Button>
                        </div>
                    </Form>
                </FeatureCard>

                <div class="space-y-6">
                    <FeatureCard info-card title="Form Component vs useForm">
                        <div class="space-y-3 text-sm text-muted-foreground">
                            <CodeBlock title="Form Component">
                                <textarea>
                                <Form
                                  action="/endpoint"
                                  method="post"
                                  :validation-timeout="500"
                                  #default="{ validate, valid, invalid, ... }"
                                >
                                </textarea>
                            </CodeBlock>
                            <CodeBlock
                                title="useForm"
                                code="
                                const form = useForm('post', '/endpoint', { ... })
                                form.setValidationTimeout(500)
                                // form.validate(), form.valid(), etc.
                            "
                            />
                            <p>
                                Both approaches provide the same precognition
                                features. The
                                <code
                                    class="rounded bg-muted px-1 py-0.5 text-xs"
                                    >&lt;Form&gt;</code
                                >
                                component uses native HTML inputs and exposes
                                validation via slot props, while
                                <code
                                    class="rounded bg-muted px-1 py-0.5 text-xs"
                                    >useForm</code
                                >
                                uses
                                <code
                                    class="rounded bg-muted px-1 py-0.5 text-xs"
                                    >v-model</code
                                >
                                bindings.
                            </p>
                        </div>
                    </FeatureCard>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
