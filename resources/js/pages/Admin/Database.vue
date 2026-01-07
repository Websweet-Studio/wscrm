<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Database, Download, Upload } from 'lucide-vue-next';

const form = useForm({
    file: null as File | null,
});

const submitImport = () => {
    if (!form.file) return;
    form.post('/admin/database/import', {
        forceFormData: true,
        onSuccess: () => {
            form.reset('file');
        },
    });
};
</script>

<template>
    <Head title="Database" />
    <AppLayout :breadcrumbs="[{ title: 'Database', href: '/admin/database' }]">
        <div class="space-y-4 sm:space-y-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <Database class="h-5 w-5" />
                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Database</h1>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Export Database</CardTitle>
                        <CardDescription>Unduh snapshot database dalam format JSON</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Button variant="outline" asChild class="cursor-pointer">
                            <a href="/admin/database/export">
                                <Download class="mr-2 h-4 w-4" />
                                Export
                            </a>
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Import Database</CardTitle>
                        <CardDescription>Unggah file JSON hasil export untuk restore data</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex items-center gap-3">
                            <input
                                type="file"
                                accept=".json,application/json"
                                @change="(e: any) => (form.file = e.target.files?.[0] ?? null)"
                                class="text-sm"
                            />
                            <Button :disabled="!form.file || form.processing" @click="submitImport" class="cursor-pointer">
                                <Upload class="mr-2 h-4 w-4" />
                                Import
                            </Button>
                        </div>
                        <div v-if="form.errors.file" class="mt-2 text-sm text-red-600">{{ form.errors.file }}</div>
                        <div v-if="form.recentlySuccessful" class="mt-2 text-sm text-green-600">Import berhasil</div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
