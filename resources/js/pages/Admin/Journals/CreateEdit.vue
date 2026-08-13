<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface WebsiteClient { id: number; name: string; }

interface Props {
    websites: WebsiteClient[];
    journal?: { id: number; website_client_id: number; entry_date: string; activities: any[]; summary: string | null; } | null;
}

const props = defineProps<Props>();
const isEdit = computed(() => !!props.journal);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Jurnal', href: '/admin/journals' },
    { title: isEdit.value ? 'Edit Jurnal' : 'Catat Jurnal', href: '#' },
];

const activityTypes = [
    { value: 'wp_update', label: 'Update WP' },
    { value: 'plugin_update', label: 'Update Plugin' },
    { value: 'theme_update', label: 'Update Tema' },
    { value: 'article', label: 'Artikel' },
    { value: 'page_optimization', label: 'Optimasi Halaman' },
    { value: 'other', label: 'Lainnya' },
];

const form = useForm({
    website_client_id: props.journal?.website_client_id || null as (number | null),
    entry_date: props.journal?.entry_date || new Date().toISOString().split('T')[0],
    activities: (props.journal?.activities as any[]) || [],
    summary: props.journal?.summary || '',
    _method: isEdit.value ? 'PUT' : 'POST',
});

const submitUrl = computed(() => isEdit.value ? `/admin/journals/${props.journal?.id}` : '/admin/journals');

const addActivity = () => { form.activities.push({ type: '' }); };
const removeActivity = (i: number) => { form.activities.splice(i, 1); };
const setType = (i: number, type: string) => { form.activities[i] = { ...form.activities[i], type }; };
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="isEdit ? 'Edit Jurnal' : 'Catat Jurnal Baru'" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <h1 class="text-3xl font-medium tracking-tight" style="font-family: Georgia, serif;">{{ isEdit ? 'Edit Jurnal' : 'Catat Jurnal Harian' }}</h1>
                <p class="text-muted-foreground">Satu entry untuk satu website per hari.</p>
            </div>

            <form @submit.prevent="form.post(submitUrl)" class="space-y-6">
                <Card class="overflow-visible">
                    <CardHeader><CardTitle class="text-base">Website & Tanggal</CardTitle></CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label>Website *</Label>
                            <select v-model="form.website_client_id" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                <option :value="null" disabled>Pilih website</option>
                                <option v-for="w in websites" :key="w.id" :value="w.id">{{ w.name }}</option>
                            </select>
                            <p v-if="form.errors.website_client_id" class="text-xs text-red-500 mt-1">{{ form.errors.website_client_id }}</p>
                        </div>
                        <div>
                            <Label>Tanggal *</Label>
                            <DatePicker v-model="form.entry_date" placeholder="Pilih tanggal" />
                            <p v-if="form.errors.entry_date" class="text-xs text-red-500 mt-1">{{ form.errors.entry_date }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                        <div>
                            <CardTitle class="text-base">Aktivitas</CardTitle>
                            <CardDescription>Tambahkan aktivitas yang dilakukan hari ini.</CardDescription>
                        </div>
                        <Button type="button" variant="outline" size="sm" @click="addActivity" class="cursor-pointer">
                            <Plus class="mr-2 h-4 w-4" /> Tambah
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <p v-if="typeof form.errors.activities === 'string'" class="text-sm text-red-500 mb-4">{{ form.errors.activities }}</p>

                        <div v-if="form.activities.length === 0" class="text-center text-muted-foreground py-8 border border-dashed rounded-lg">
                            Belum ada aktivitas. Klik "Tambah" untuk menambahkan.
                        </div>

                        <div v-for="(a, idx) in form.activities" :key="idx" class="border rounded-lg p-4 mb-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium">Aktivitas {{ idx + 1 }}</span>
                                <Button type="button" variant="ghost" size="icon" class="h-7 w-7 cursor-pointer" @click="removeActivity(idx)">
                                    <Trash2 class="h-4 w-4 text-destructive" />
                                </Button>
                            </div>
                            <div>
                                <Label>Tipe *</Label>
                                <select :value="a.type" @change="setType(idx, ($event.target as HTMLSelectElement).value)" class="mt-1 w-full rounded-md border border-border bg-input px-3 py-2 text-sm text-foreground">
                                    <option value="" disabled>Pilih tipe aktivitas</option>
                                    <option v-for="t in activityTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
                                </select>
                            </div>

                            <template v-if="a.type === 'wp_update'">
                                <div class="grid grid-cols-2 gap-3">
                                    <div><Label>Versi Sebelum</Label><Input v-model="a.from_version" placeholder="6.5" /></div>
                                    <div><Label>Versi Sesudah</Label><Input v-model="a.to_version" placeholder="6.6" /></div>
                                </div>
                                <div><Label>Catatan</Label><Input v-model="a.note" placeholder="Catatan opsional..." /></div>
                            </template>

                            <template v-if="a.type === 'plugin_update'">
                                <div><Label>Nama Plugin</Label><Input v-model="a.plugin" placeholder="Yoast SEO" /></div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div><Label>Versi Sebelum</Label><Input v-model="a.from_version" placeholder="21.0" /></div>
                                    <div><Label>Versi Sesudah</Label><Input v-model="a.to_version" placeholder="22.0" /></div>
                                </div>
                            </template>

                            <template v-if="a.type === 'theme_update'">
                                <div><Label>Nama Tema</Label><Input v-model="a.theme" placeholder="Divi" /></div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div><Label>Versi Sebelum</Label><Input v-model="a.from_version" placeholder="4.27" /></div>
                                    <div><Label>Versi Sesudah</Label><Input v-model="a.to_version" placeholder="4.28" /></div>
                                </div>
                            </template>

                            <template v-if="a.type === 'article'">
                                <div><Label>Judul Artikel</Label><Input v-model="a.title" placeholder="Judul artikel" /></div>
                                <div><Label>URL</Label><Input v-model="a.url" placeholder="https://..." /></div>
                                <div><Label>Jumlah Kata</Label><Input v-model.number="a.word_count" type="number" placeholder="800" /></div>
                            </template>

                            <template v-if="a.type === 'page_optimization'">
                                <div><Label>Nama Halaman</Label><Input v-model="a.page" placeholder="Landing Page" /></div>
                                <div><Label>Detail Optimasi</Label><Textarea v-model="a.detail" rows="2" placeholder="Optimasi SEO, load speed..." /></div>
                            </template>

                            <template v-if="a.type === 'other'">
                                <div><Label>Deskripsi</Label><Textarea v-model="a.description" rows="2" placeholder="Deskripsikan aktivitas..." /></div>
                            </template>

                            <template v-if="a.type && a.type !== 'other'">
                                <div><Label>Deskripsi</Label><Textarea v-model="a.description" rows="2" placeholder="Deskripsikan aktivitas..." /></div>
                            </template>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">Ringkasan</CardTitle>
                        <CardDescription>Opsional - catatan tambahan untuk hari ini.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <Textarea v-model="form.summary" rows="3" placeholder="Ringkasan aktivitas hari ini..." />
                        <p v-if="form.errors.summary" class="text-xs text-red-500 mt-1">{{ form.errors.summary }}</p>
                    </CardContent>
                </Card>

                <div class="flex justify-end gap-3">
                    <Button type="button" variant="outline" @click="$inertia.visit('/admin/journals')" class="cursor-pointer">Batal</Button>
                    <Button type="submit" :disabled="form.processing" class="cursor-pointer">{{ isEdit ? 'Simpan' : 'Catat Jurnal' }}</Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
