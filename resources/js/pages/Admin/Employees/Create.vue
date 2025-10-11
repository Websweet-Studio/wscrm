<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import DatePicker from '@/components/ui/date-picker/DatePicker.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Users, Building2 } from 'lucide-vue-next';

interface Props {
    departments?: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Karyawan', href: '/admin/employees' },
    { title: 'Tambah Karyawan', href: '/admin/employees/create' },
];

const form = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
    nik: '',
    position: '',
    department: '',
    phone: '',
    address: '',
    hire_date: '',
    salary: '',
    status: 'active' as 'active' | 'inactive' | 'terminated',
    notes: '',
});

const submit = () => {
    form.post('/admin/employees', {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Admin - Tambah Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <Button variant="outline" size="sm" asChild>
                    <Link href="/admin/employees" class="cursor-pointer">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Kembali
                    </Link>
                </Button>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Tambah Karyawan Baru</h1>
                    <p class="text-sm sm:text-base text-muted-foreground">Daftarkan karyawan internal baru</p>
                </div>
            </div>

            <div class="max-w-4xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- User Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Users class="mr-2 h-5 w-5" />
                                Informasi Akun
                            </CardTitle>
                            <CardDescription>Data login untuk akses sistem</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label for="name">Nama Lengkap *</Label>
                                    <Input
                                        id="name"
                                        autocomplete="name"
                                        v-model="form.name"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-500">{{ form.errors.name }}</p>
                                </div>
                                <div>
                                    <Label for="email">Email *</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        autocomplete="email"
                                        v-model="form.email"
                                        :class="{ 'border-red-500': form.errors.email }"
                                        required
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-500">{{ form.errors.email }}</p>
                                </div>
                                <div>
                                    <Label for="username">Username *</Label>
                                    <Input
                                        id="username"
                                        autocomplete="username"
                                        v-model="form.username"
                                        :class="{ 'border-red-500': form.errors.username }"
                                        required
                                    />
                                    <p v-if="form.errors.username" class="mt-1 text-xs text-red-500">{{ form.errors.username }}</p>
                                </div>
                                <div>
                                    <Label for="password">Password *</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        autocomplete="new-password"
                                        v-model="form.password"
                                        :class="{ 'border-red-500': form.errors.password }"
                                        required
                                    />
                                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-500">{{ form.errors.password }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Label for="password_confirmation">Konfirmasi Password *</Label>
                                <Input
                                    id="password_confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    v-model="form.password_confirmation"
                                    :class="{ 'border-red-500': form.errors.password_confirmation }"
                                    required
                                />
                                <p v-if="form.errors.password_confirmation" class="mt-1 text-xs text-red-500">{{ form.errors.password_confirmation }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Employee Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Building2 class="mr-2 h-5 w-5" />
                                Informasi Karyawan
                            </CardTitle>
                            <CardDescription>Data pekerjaan dan personal karyawan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label for="nik">NIK *</Label>
                                    <Input
                                        id="nik"
                                        v-model="form.nik"
                                        placeholder="Contoh: EMP001"
                                        :class="{ 'border-red-500': form.errors.nik }"
                                        required
                                    />
                                    <p v-if="form.errors.nik" class="mt-1 text-xs text-red-500">{{ form.errors.nik }}</p>
                                </div>
                                <div>
                                    <Label for="position">Jabatan *</Label>
                                    <Input
                                        id="position"
                                        v-model="form.position"
                                        placeholder="Contoh: Software Developer"
                                        :class="{ 'border-red-500': form.errors.position }"
                                        required
                                    />
                                    <p v-if="form.errors.position" class="mt-1 text-xs text-red-500">{{ form.errors.position }}</p>
                                </div>
                                <div>
                                    <Label for="department">Departemen *</Label>
                                    <Input
                                        id="department"
                                        v-model="form.department"
                                        list="departments-list"
                                        placeholder="Contoh: IT"
                                        :class="{ 'border-red-500': form.errors.department }"
                                        required
                                    />
                                    <datalist id="departments-list">
                                        <option v-for="dept in departments" :key="dept" :value="dept" />
                                    </datalist>
                                    <p v-if="form.errors.department" class="mt-1 text-xs text-red-500">{{ form.errors.department }}</p>
                                </div>
                                <div>
                                    <Label for="hire_date">Tanggal Bergabung *</Label>
                                    <DatePicker
                                        id="hire_date"
                                        v-model="form.hire_date"
                                        :error="!!form.errors.hire_date"
                                        placeholder="Pilih tanggal bergabung"
                                    />
                                    <p v-if="form.errors.hire_date" class="mt-1 text-xs text-red-500">{{ form.errors.hire_date }}</p>
                                </div>
                                <div>
                                    <Label for="phone">Telepon</Label>
                                    <Input
                                        id="phone"
                                        v-model="form.phone"
                                        placeholder="Contoh: 08123456789"
                                        :class="{ 'border-red-500': form.errors.phone }"
                                    />
                                    <p v-if="form.errors.phone" class="mt-1 text-xs text-red-500">{{ form.errors.phone }}</p>
                                </div>
                                <div>
                                    <Label for="salary">Gaji Pokok</Label>
                                    <Input
                                        id="salary"
                                        type="number"
                                        v-model="form.salary"
                                        placeholder="Contoh: 5000000"
                                        :class="{ 'border-red-500': form.errors.salary }"
                                    />
                                    <p v-if="form.errors.salary" class="mt-1 text-xs text-red-500">{{ form.errors.salary }}</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <Label for="address">Alamat</Label>
                                <Input
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Alamat lengkap karyawan"
                                    :class="{ 'border-red-500': form.errors.address }"
                                />
                                <p v-if="form.errors.address" class="mt-1 text-xs text-red-500">{{ form.errors.address }}</p>
                            </div>
                            <div class="mt-4">
                                <Label for="status">Status *</Label>
                                <select
                                    id="status"
                                    v-model="form.status"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                    <option value="terminated">Berhenti</option>
                                </select>
                                <p v-if="form.errors.status" class="mt-1 text-xs text-red-500">{{ form.errors.status }}</p>
                            </div>
                            <div class="mt-4">
                                <Label for="notes">Catatan</Label>
                                <textarea
                                    id="notes"
                                    v-model="form.notes"
                                    rows="3"
                                    class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                    :class="{ 'border-red-500': form.errors.notes }"
                                    placeholder="Catatan tambahan tentang karyawan"
                                ></textarea>
                                <p v-if="form.errors.notes" class="mt-1 text-xs text-red-500">{{ form.errors.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" asChild>
                            <Link href="/admin/employees" class="cursor-pointer">Batal</Link>
                        </Button>
                        <Button type="submit" :disabled="form.processing" class="cursor-pointer">
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Karyawan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>