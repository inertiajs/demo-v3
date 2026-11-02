<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Upload, X } from '@lucide/vue';
import FeatureCard from '@/components/FeatureCard.vue';
import FeatureHeader from '@/components/FeatureHeader.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { submitFileUploads } from '@/wayfinder/App/Http/Controllers/Feature/FormController';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Forms' },
    { title: 'File Uploads' },
];

const form = useForm({
    photo: null as File | null,
    files: [] as File[],
});

function onPhotoChange(event: Event) {
    const target = event.target as HTMLInputElement;
    form.photo = target.files?.[0] ?? null;
}

function onFilesChange(event: Event) {
    const target = event.target as HTMLInputElement;
    form.files = Array.from(target.files ?? []);
}

function removeFile(index: number) {
    form.files.splice(index, 1);
}

function submit() {
    form.submit(submitFileUploads(), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
}

function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return bytes + ' B';
    }
    if (bytes < 1024 * 1024) {
        return (bytes / 1024).toFixed(1) + ' KB';
    }
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}
</script>

<template>
    <Head title="File Uploads" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <FeatureHeader
                title="File Uploads"
                docs="the-basics/file-uploads"
                controller="app/Http/Controllers/Feature/FormController.php#L42"
            >
                File upload handling with progress tracking using useForm.
            </FeatureHeader>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Upload Form -->
                <FeatureCard
                    title="Upload Files"
                    description="Single photo upload and multiple file uploads with progress tracking."
                >
                    <form class="space-y-6" @submit.prevent="submit">
                        <!-- Single File (Photo) -->
                        <div class="space-y-2">
                            <Label for="photo">Photo (Single Image)</Label>
                            <div class="flex items-center gap-3">
                                <label
                                    for="photo"
                                    class="flex cursor-pointer items-center gap-2 rounded-md border border-dashed border-black/10 px-4 py-3 text-sm text-muted-foreground transition-colors hover:border-primary hover:text-primary dark:border-white/10"
                                >
                                    <Upload class="size-4" />
                                    {{
                                        form.photo
                                            ? form.photo.name
                                            : 'Choose image...'
                                    }}
                                </label>
                                <input
                                    id="photo"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="onPhotoChange"
                                />
                                <Button
                                    v-if="form.photo"
                                    variant="ghost"
                                    size="sm"
                                    @click="form.photo = null"
                                >
                                    <X class="size-4" />
                                </Button>
                            </div>
                            <p
                                v-if="form.photo"
                                class="text-xs text-muted-foreground"
                            >
                                {{ formatFileSize(form.photo.size) }}
                            </p>
                            <InputError :message="form.errors.photo" />
                        </div>

                        <!-- Multiple Files -->
                        <div class="space-y-2">
                            <Label for="files"
                                >Documents (Multiple Files)</Label
                            >
                            <div>
                                <label
                                    for="files"
                                    class="flex cursor-pointer items-center gap-2 rounded-md border border-dashed border-black/10 px-4 py-3 text-sm text-muted-foreground transition-colors hover:border-primary hover:text-primary dark:border-white/10"
                                >
                                    <Upload class="size-4" />
                                    Choose files... (max 5)
                                </label>
                                <input
                                    id="files"
                                    type="file"
                                    multiple
                                    class="hidden"
                                    @change="onFilesChange"
                                />
                            </div>

                            <!-- File List -->
                            <div
                                v-if="form.files.length"
                                class="space-y-1 pt-1"
                            >
                                <div
                                    v-for="(file, index) in form.files"
                                    :key="index"
                                    class="flex items-center justify-between rounded bg-muted px-3 py-1.5 text-sm"
                                >
                                    <span class="truncate">{{
                                        file.name
                                    }}</span>
                                    <div class="flex items-center gap-2">
                                        <span
                                            class="text-xs text-muted-foreground"
                                            >{{
                                                formatFileSize(file.size)
                                            }}</span
                                        >
                                        <button
                                            type="button"
                                            class="text-muted-foreground hover:text-foreground"
                                            @click="removeFile(index)"
                                        >
                                            <X class="size-3" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <InputError :message="form.errors.files" />
                        </div>

                        <!-- Progress Bar -->
                        <div v-if="form.progress" class="space-y-1">
                            <div
                                class="flex items-center justify-between text-sm"
                            >
                                <span>Uploading...</span>
                                <span>{{ form.progress.percentage }}%</span>
                            </div>
                            <div class="w-full rounded-full bg-secondary">
                                <div
                                    class="h-3 rounded-full bg-primary transition-all"
                                    :style="{
                                        width: `${form.progress.percentage}%`,
                                    }"
                                />
                            </div>
                        </div>

                        <!-- Submit -->
                        <Button
                            type="submit"
                            :disabled="
                                form.processing ||
                                (!form.photo && form.files.length === 0)
                            "
                        >
                            {{ form.processing ? 'Uploading...' : 'Upload' }}
                        </Button>
                    </form>
                </FeatureCard>

                <!-- State Panel -->
                <div class="space-y-6">
                    <FeatureCard info-card title="Upload State">
                        <template #description>
                            Tip: If you're on a fast network, throttle it in
                            DevTools (Network &rarr; Slow 3G) to see the
                            progress percentage in action.
                        </template>
                        <div class="space-y-3">
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
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium"
                                    >progress</span
                                >
                                <Badge variant="secondary">{{
                                    form.progress
                                        ? `${form.progress.percentage}%`
                                        : 'null'
                                }}</Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">isDirty</span>
                                <Badge
                                    :variant="
                                        form.isDirty ? 'default' : 'secondary'
                                    "
                                    >{{ form.isDirty }}</Badge
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
                                    >wasSuccessful</span
                                >
                                <Badge
                                    :variant="
                                        form.wasSuccessful
                                            ? 'default'
                                            : 'secondary'
                                    "
                                    >{{ form.wasSuccessful }}</Badge
                                >
                            </div>
                        </div>
                    </FeatureCard>

                    <FeatureCard info-card title="Selected Files">
                        <div
                            v-if="form.photo || form.files.length"
                            class="space-y-2 text-sm"
                        >
                            <div v-if="form.photo">
                                <span class="font-medium">Photo: </span
                                >{{ form.photo.name }} ({{
                                    formatFileSize(form.photo.size)
                                }})
                            </div>
                            <div v-if="form.files.length">
                                <span class="font-medium">Files: </span
                                >{{ form.files.length }} selected
                            </div>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No files selected.
                        </p>
                    </FeatureCard>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
