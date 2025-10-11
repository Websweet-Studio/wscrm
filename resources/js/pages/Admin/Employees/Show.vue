<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Building2, Calendar, DollarSign, Mail, MapPin, Phone, User, Key, Edit, Trash2 } from 'lucide-vue-next';

interface Employee {
    id: number;
    user_id: number;
    nik: string;
    position: string;
    department: string;
    phone?: string;
    address?: string;
    hire_date: string;
    salary?: number;
    status: 'active' | 'inactive' | 'terminated';
    notes?: string;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        username?: string;
        created_at: string;
    };
}

interface Props {
    employee: Employee;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Karyawan', href: '/admin/employees' },
    { title: props.employee.user.name, href: `/admin/employees/${props.employee.id}` },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const formatCurrency = (amount: number | string | undefined) => {
    if (!amount) return '-';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(amount));
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'inactive':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        case 'terminated':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const getStatusText = (status: string) => {
    switch (status) {
        case 'active':
            return 'Aktif';
        case 'inactive':
            return 'Tidak Aktif';
        case 'terminated':
            return 'Berhenti';
        default:
            return status;
    }
};

const resetPassword = () => {
    if (confirm(`Reset password untuk ${props.employee.user.name}? Password baru akan dikirim ke email ${props.employee.user.email}`)) {
        router.post(`/admin/employees/${props.employee.id}/reset-password`, {}, {
            onSuccess: () => {
                alert('Password berhasil direset. Email dengan password baru telah dikirim.');
            },
            onError: (errors) => {
                console.error('Reset password error:', errors);
                alert('Gagal mereset password. Silakan coba lagi.');
            },
        });
    }
};

const deleteEmployee = () => {
    if (confirm(`Apakah Anda yakin ingin menghapus karyawan ${props.employee.user.name}? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/employees/${props.employee.id}`, {
            onSuccess: () => {
                router.visit('/admin/employees');
            },
            onError: (errors) => {
                console.error('Delete employee error:', errors);
                alert('Gagal menghapus karyawan. Silakan coba lagi.');
            },
        });
    }
};
</script>

<template>
    <Head title="Admin - Detail Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <Button variant="outline" size="sm" asChild>
                        <Link href="/admin/employees" class="cursor-pointer">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">{{ employee.user.name }}</h1>
                        <p class="text-sm sm:text-base text-muted-foreground">Detail informasi karyawan</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="secondary" @click="resetPassword" class="cursor-pointer">
                        <Key class="mr-2 h-4 w-4" />
                        Reset Password
                    </Button>
                    <Button variant="outline" asChild>
                        <Link :href="`/admin/employees/${employee.id}/edit`" class="cursor-pointer">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="outline" @click="deleteEmployee" class="cursor-pointer text-red-600 hover:text-red-700">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Hapus
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Main Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Personal Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <User class="mr-2 h-5 w-5" />
                                Informasi Pribadi
                            </CardTitle>
                            <CardDescription>Data diri karyawan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Nama Lengkap</label>
                                    <p class="text-base font-medium">{{ employee.user.name }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">NIK</label>
                                    <p class="text-base font-medium">{{ employee.nik }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Email</label>
                                    <div class="flex items-center gap-2">
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                        <p class="text-base">{{ employee.user.email }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Username</label>
                                    <p class="text-base">{{ employee.user.username || '-' }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Telepon</label>
                                    <div class="flex items-center gap-2">
                                        <Phone class="h-4 w-4 text-muted-foreground" />
                                        <p class="text-base">{{ employee.phone || '-' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Alamat</label>
                                    <div class="flex items-start gap-2">
                                        <MapPin class="h-4 w-4 text-muted-foreground mt-0.5" />
                                        <p class="text-base">{{ employee.address || '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Employment Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center">
                                <Building2 class="mr-2 h-5 w-5" />
                                Informasi Pekerjaan
                            </CardTitle>
                            <CardDescription>Data employment karyawan</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Jabatan</label>
                                    <p class="text-base font-medium">{{ employee.position }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Departemen</label>
                                    <p class="text-base font-medium">{{ employee.department }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Tanggal Bergabung</label>
                                    <div class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-muted-foreground" />
                                        <p class="text-base">{{ formatDate(employee.hire_date) }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Gaji Pokok</label>
                                    <div class="flex items-center gap-2">
                                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                                        <p class="text-base font-medium">{{ formatCurrency(employee.salary) }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Status</label>
                                    <div>
                                        <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusClass(employee.status)}`">
                                            {{ getStatusText(employee.status) }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-muted-foreground">Lama Bekerja</label>
                                    <p class="text-base">
                                        {{ Math.floor((new Date().getTime() - new Date(employee.hire_date).getTime()) / (1000 * 60 * 60 * 24 * 30)) }} bulan
                                    </p>
                                </div>
                            </div>
                            <div v-if="employee.notes" class="mt-4">
                                <label class="text-sm font-medium text-muted-foreground">Catatan</label>
                                <p class="text-base mt-1 p-3 bg-gray-50 dark:bg-gray-800 rounded-md">{{ employee.notes }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Status Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Status Karyawan</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-center">
                                <span :class="`inline-flex items-center rounded-full px-3 py-2 text-sm font-medium ${getStatusClass(employee.status)}`">
                                    {{ getStatusText(employee.status) }}
                                </span>
                                <p class="mt-2 text-sm text-muted-foreground">
                                    {{ employee.status === 'active' ? 'Karyawan aktif dan dapat mengakses sistem' :
                                       employee.status === 'inactive' ? 'Karyawan tidak aktif sementara' :
                                       'Karyawan telah berhenti bekerja' }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Aksi Cepat</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button @click="resetPassword" variant="outline" class="w-full cursor-pointer justify-start">
                                <Key class="mr-2 h-4 w-4" />
                                Reset Password
                            </Button>
                            <Button asChild variant="outline" class="w-full cursor-pointer justify-start">
                                <Link :href="`/admin/employees/${employee.id}/edit`">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Data
                                </Link>
                            </Button>
                        </CardContent>
                    </Card>

                    <!-- System Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Informasi Sistem</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">ID Karyawan</label>
                                <p class="text-base font-mono">#{{ employee.id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">ID User</label>
                                <p class="text-base font-mono">#{{ employee.user_id }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Dibuat</label>
                                <p class="text-base">{{ formatDate(employee.created_at) }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-muted-foreground">Diperbarui</label>
                                <p class="text-base">{{ formatDate(employee.updated_at) }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>