<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Database, Download, Upload } from 'lucide-vue-next';

const form = useForm({
    file: null as File | null,
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Database', href: '/admin/database' },
];

const submitImport = () => {
    if (!form.file) return;
    form.post('/admin/database/import', {
        forceFormData: true,
        onSuccess: () => {
            form.reset('file');
        },
    });
};

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement | null;
    form.file = target?.files?.[0] ?? null;
};
</script>

<template>
    <Head title="Admin - Database" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <Database class="h-5 w-5" />
                        <h1 class="text-2xl font-medium tracking-tight sm:text-3xl" style="font-family: Georgia, serif;">Database</h1>
                    </div>
                    <p class="text-sm text-muted-foreground sm:text-base">Export dan import snapshot database.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle style="font-family: Georgia, serif;">Export Database</CardTitle>
                        <CardDescription>Unduh snapshot database dalam format JSON</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button variant="outline" asChild class="w-full cursor-pointer sm:w-auto">
                            <a href="/admin/database/export">
                                <Download class="mr-2 h-4 w-4" />
                                Export
                            </a>
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle style="font-family: Georgia, serif;">Import Database</CardTitle>
                        <CardDescription>Unggah file JSON hasil export untuk restore data</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                            <div class="w-full space-y-2">
                                <Label for="database-import-file">File JSON</Label>
                                <input
                                    id="database-import-file"
                                    type="file"
                                    accept=".json,application/json"
                                    @change="onFileChange"
                                    class="block w-full cursor-pointer rounded-md border border-input bg-background px-3 py-2 text-sm text-foreground file:mr-4 file:cursor-pointer file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-2 file:text-sm file:font-medium file:text-secondary-foreground hover:file:bg-secondary/80 dark:bg-input/30"
                                />
                            </div>
                            <Button :disabled="!form.file || form.processing" @click="submitImport" class="w-full cursor-pointer disabled:cursor-not-allowed sm:w-auto">
                                <Upload class="mr-2 h-4 w-4" />
                                Import
                            </Button>
                        </div>
                        <div v-if="form.errors.file" class="mt-2 text-sm text-destructive">{{ form.errors.file }}</div>
                        <div v-if="form.recentlySuccessful" class="mt-2 text-sm text-primary">Import berhasil</div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
