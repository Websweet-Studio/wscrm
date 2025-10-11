<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Clock, Edit, Plus, Search, Trash2, UserCheck, Users, UserX, X, Key, Building2, Calendar, DollarSign } from 'lucide-vue-next';
import { ref, watch } from 'vue';

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
    };
}

interface Props {
    employees?: {
        data: Employee[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    departments?: string[];
    filters?: {
        search?: string;
        department?: string;
        status?: string;
    };
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const department = ref(props.filters?.department || '');
const status = ref(props.filters?.status || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedEmployee = ref<Employee | null>(null);
const employeeToDelete = ref<Employee | null>(null);

const createForm = useForm({
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

const editForm = useForm({
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

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Karyawan', href: '/admin/employees' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
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

const handleSearch = () => {
    router.get(
        '/admin/employees',
        {
            search: search.value,
            department: department.value,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'terminated':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'inactive':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
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

const submitCreate = () => {
    createForm.post('/admin/employees', {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        },
        onError: (errors) => {
            console.error('Create employee error:', errors);
        },
    });
};

const openEditModal = (employee: Employee) => {
    selectedEmployee.value = employee;
    editForm.reset();
    editForm.name = employee.user.name;
    editForm.email = employee.user.email;
    editForm.username = employee.user.username || '';
    editForm.nik = employee.nik;
    editForm.position = employee.position;
    editForm.department = employee.department;
    editForm.phone = employee.phone || '';
    editForm.address = employee.address || '';
    editForm.hire_date = employee.hire_date;
    editForm.salary = employee.salary?.toString() || '';
    editForm.status = employee.status;
    editForm.notes = employee.notes || '';
    editForm.password = '';
    editForm.password_confirmation = '';
    showEditModal.value = true;
};

const submitEdit = () => {
    if (!selectedEmployee.value) return;

    const formData = editForm.transform((data) => {
        const result = { ...data };
        if (!data.password) {
            delete result.password;
            delete result.password_confirmation;
        }
        return result;
    });

    formData.put(`/admin/employees/${selectedEmployee.value.id}`, {
        onSuccess: () => {
            showEditModal.value = false;
            editForm.reset();
            selectedEmployee.value = null;
        },
        onError: (errors) => {
            console.error('Update employee error:', errors);
        },
    });
};

const openDeleteModal = (employee: Employee) => {
    employeeToDelete.value = employee;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!employeeToDelete.value) return;

    router.delete(`/admin/employees/${employeeToDelete.value.id}`, {
        onSuccess: () => {
            showDeleteModal.value = false;
            employeeToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete employee error:', errors);
        },
    });
};

const resetPassword = (employee: Employee) => {
    if (confirm(`Reset password untuk ${employee.user.name}? Password baru akan dikirim ke email ${employee.user.email}`)) {
        router.post(`/admin/employees/${employee.id}/reset-password`, {}, {
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
</script>

<template>
    <Head title="Admin - Kelola Karyawan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight">Kelola Karyawan</h1>
                    <p class="text-sm sm:text-base text-muted-foreground">Kelola akun dan data karyawan internal</p>
                </div>
                <Button @click="showCreateModal = true" class="cursor-pointer w-full sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" />
                    <span class="hidden sm:inline">Tambah Karyawan</span>
                    <span class="sm:hidden">Tambah</span>
                </Button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs sm:text-sm font-medium">Total Karyawan</CardTitle>
                        <Users class="h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl sm:text-2xl font-bold">{{ employees?.total || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs sm:text-sm font-medium">Aktif</CardTitle>
                        <UserCheck class="h-3 w-3 sm:h-4 sm:w-4 text-green-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl sm:text-2xl font-bold text-green-600">
                            {{ employees?.data?.filter((e) => e.status === 'active').length || 0 }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs sm:text-sm font-medium">Tidak Aktif</CardTitle>
                        <UserX class="h-3 w-3 sm:h-4 sm:w-4 text-gray-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl sm:text-2xl font-bold text-gray-600">
                            {{ employees?.data?.filter((e) => e.status === 'inactive').length || 0 }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs sm:text-sm font-medium">Berhenti</CardTitle>
                        <Clock class="h-3 w-3 sm:h-4 sm:w-4 text-red-600" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl sm:text-2xl font-bold text-red-600">
                            {{ employees?.data?.filter((e) => e.status === 'terminated').length || 0 }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Employee List -->
            <Card>
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg sm:text-xl">Data Karyawan</CardTitle>
                    <CardDescription class="text-sm">Kelola informasi karyawan internal</CardDescription>
                </CardHeader>
                <CardContent class="px-3 sm:px-4 lg:px-6">
                    <!-- Search and Filter -->
                    <div class="mb-4 sm:mb-6 flex flex-col gap-3 sm:gap-4 sm:flex-row">
                        <div class="relative flex-1 max-w-none sm:max-w-sm">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari karyawan..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <select
                            v-model="department"
                            class="flex h-9 w-full sm:w-[180px] cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Semua Departemen</option>
                            <option v-for="dept in departments" :key="dept" :value="dept">{{ dept }}</option>
                        </select>
                        <select
                            v-model="status"
                            class="flex h-9 w-full sm:w-[180px] cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="terminated">Berhenti</option>
                        </select>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <!-- Employee Table -->
                    <div v-if="!employees?.data || employees.data.length === 0" class="py-12 text-center text-muted-foreground">
                        <Users class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Karyawan tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Coba sesuaikan kriteria pencarian Anda.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">NIK</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Nama</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Email</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Jabatan</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Departemen</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Telepon</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Gaji</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Status</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Bergabung</th>
                                    <th class="px-3 py-3 text-center text-xs font-medium text-muted-foreground uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="employee in employees.data"
                                    :key="employee.id"
                                    class="border-b border-border hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-3 py-4 text-sm text-foreground font-medium">{{ employee.nik }}</td>
                                    <td class="px-3 py-4 text-sm text-foreground font-medium">{{ employee.user.name }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ employee.user.email }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ employee.position }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ employee.department }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ employee.phone || '-' }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ formatCurrency(employee.salary) }}</td>
                                    <td class="px-3 py-4">
                                        <span :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusClass(employee.status)}`">
                                            {{ getStatusText(employee.status) }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ formatDate(employee.hire_date) }}</td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center justify-center gap-1">
                                            <Button size="sm" variant="outline" asChild>
                                                <Link :href="`/admin/employees/${employee.id}`" title="Lihat Detail">
                                                    <Users class="h-3 w-3" />
                                                </Link>
                                            </Button>
                                            <Button size="sm" variant="secondary" @click="resetPassword(employee)" class="cursor-pointer" title="Reset Password">
                                                <Key class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openEditModal(employee)" class="cursor-pointer" title="Edit">
                                                <Edit class="h-3 w-3" />
                                            </Button>
                                            <Button size="sm" variant="outline" @click="openDeleteModal(employee)" class="cursor-pointer" title="Hapus">
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="employees?.links && employees.links.length > 3" class="flex items-center justify-between border-t pt-6">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (employees.current_page - 1) * employees.per_page + 1 || 0 }} to
                            {{ Math.min(employees.current_page * employees.per_page, employees.total) || 0 }} of {{ employees.total || 0 }} results
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="link in employees.links" :key="link.label">
                                <Button
                                    v-if="link.url"
                                    variant="outline"
                                    size="sm"
                                    :disabled="!link.url"
                                    :class="link.active ? 'cursor-pointer bg-primary text-primary-foreground' : 'cursor-pointer'"
                                    @click="router.visit(link.url)"
                                    v-html="link.label"
                                />
                                <span v-else class="px-3 py-2 text-sm text-muted-foreground" v-html="link.label" />
                            </template>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Create Employee Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>
            <div class="relative mx-4 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Tambah Karyawan Baru</h2>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <!-- User Information -->
                    <div class="border-b pb-4">
                        <h3 class="text-sm font-medium mb-3 flex items-center">
                            <Users class="h-4 w-4 mr-2" />
                            Informasi Akun
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="create-name">Nama *</Label>
                                <Input
                                    id="create-name"
                                    autocomplete="name"
                                    v-model="createForm.name"
                                    :class="{ 'border-red-500': createForm.errors.name }"
                                    required
                                />
                                <p v-if="createForm.errors.name" class="mt-1 text-xs text-red-500">{{ createForm.errors.name }}</p>
                            </div>
                            <div>
                                <Label for="create-email">Email *</Label>
                                <Input
                                    id="create-email"
                                    type="email"
                                    autocomplete="email"
                                    v-model="createForm.email"
                                    :class="{ 'border-red-500': createForm.errors.email }"
                                    required
                                />
                                <p v-if="createForm.errors.email" class="mt-1 text-xs text-red-500">{{ createForm.errors.email }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="create-username">Username *</Label>
                                <Input
                                    id="create-username"
                                    autocomplete="username"
                                    v-model="createForm.username"
                                    :class="{ 'border-red-500': createForm.errors.username }"
                                    required
                                />
                                <p v-if="createForm.errors.username" class="mt-1 text-xs text-red-500">{{ createForm.errors.username }}</p>
                            </div>
                            <div>
                                <Label for="create-password">Password *</Label>
                                <Input
                                    id="create-password"
                                    type="password"
                                    autocomplete="new-password"
                                    v-model="createForm.password"
                                    :class="{ 'border-red-500': createForm.errors.password }"
                                    required
                                />
                                <p v-if="createForm.errors.password" class="mt-1 text-xs text-red-500">{{ createForm.errors.password }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Information -->
                    <div>
                        <h3 class="text-sm font-medium mb-3 flex items-center">
                            <Building2 class="h-4 w-4 mr-2" />
                            Informasi Karyawan
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="create-nik">NIK *</Label>
                                <Input
                                    id="create-nik"
                                    v-model="createForm.nik"
                                    :class="{ 'border-red-500': createForm.errors.nik }"
                                    required
                                />
                                <p v-if="createForm.errors.nik" class="mt-1 text-xs text-red-500">{{ createForm.errors.nik }}</p>
                            </div>
                            <div>
                                <Label for="create-position">Jabatan *</Label>
                                <Input
                                    id="create-position"
                                    v-model="createForm.position"
                                    :class="{ 'border-red-500': createForm.errors.position }"
                                    required
                                />
                                <p v-if="createForm.errors.position" class="mt-1 text-xs text-red-500">{{ createForm.errors.position }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="create-department">Departemen *</Label>
                                <Input
                                    id="create-department"
                                    v-model="createForm.department"
                                    :class="{ 'border-red-500': createForm.errors.department }"
                                    required
                                />
                                <p v-if="createForm.errors.department" class="mt-1 text-xs text-red-500">{{ createForm.errors.department }}</p>
                            </div>
                            <div>
                                <Label for="create-hire-date">Tanggal Bergabung *</Label>
                                <Input
                                    id="create-hire-date"
                                    type="date"
                                    v-model="createForm.hire_date"
                                    :class="{ 'border-red-500': createForm.errors.hire_date }"
                                    required
                                />
                                <p v-if="createForm.errors.hire_date" class="mt-1 text-xs text-red-500">{{ createForm.errors.hire_date }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="create-phone">Telepon</Label>
                                <Input
                                    id="create-phone"
                                    v-model="createForm.phone"
                                    :class="{ 'border-red-500': createForm.errors.phone }"
                                />
                                <p v-if="createForm.errors.phone" class="mt-1 text-xs text-red-500">{{ createForm.errors.phone }}</p>
                            </div>
                            <div>
                                <Label for="create-salary">Gaji Pokok</Label>
                                <Input
                                    id="create-salary"
                                    type="number"
                                    v-model="createForm.salary"
                                    :class="{ 'border-red-500': createForm.errors.salary }"
                                />
                                <p v-if="createForm.errors.salary" class="mt-1 text-xs text-red-500">{{ createForm.errors.salary }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Label for="create-address">Alamat</Label>
                            <Input
                                id="create-address"
                                v-model="createForm.address"
                                :class="{ 'border-red-500': createForm.errors.address }"
                            />
                            <p v-if="createForm.errors.address" class="mt-1 text-xs text-red-500">{{ createForm.errors.address }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="create-status">Status *</Label>
                                <select
                                    id="create-status"
                                    v-model="createForm.status"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                    <option value="terminated">Berhenti</option>
                                </select>
                                <p v-if="createForm.errors.status" class="mt-1 text-xs text-red-500">{{ createForm.errors.status }}</p>
                            </div>
                            <div>
                                <Label for="create-password-confirmation">Konfirmasi Password *</Label>
                                <Input
                                    id="create-password-confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    v-model="createForm.password_confirmation"
                                    required
                                />
                            </div>
                        </div>
                        <div class="mt-4">
                            <Label for="create-notes">Catatan</Label>
                            <textarea
                                id="create-notes"
                                v-model="createForm.notes"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': createForm.errors.notes }"
                            ></textarea>
                            <p v-if="createForm.errors.notes" class="mt-1 text-xs text-red-500">{{ createForm.errors.notes }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer">Batal</Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Membuat...' : 'Buat Karyawan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Employee Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>
            <div class="relative mx-4 w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Edit Karyawan</h2>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <!-- User Information -->
                    <div class="border-b pb-4">
                        <h3 class="text-sm font-medium mb-3 flex items-center">
                            <Users class="h-4 w-4 mr-2" />
                            Informasi Akun
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="edit-name">Nama *</Label>
                                <Input
                                    id="edit-name"
                                    autocomplete="name"
                                    v-model="editForm.name"
                                    :class="{ 'border-red-500': editForm.errors.name }"
                                    required
                                />
                                <p v-if="editForm.errors.name" class="mt-1 text-xs text-red-500">{{ editForm.errors.name }}</p>
                            </div>
                            <div>
                                <Label for="edit-email">Email *</Label>
                                <Input
                                    id="edit-email"
                                    type="email"
                                    autocomplete="email"
                                    v-model="editForm.email"
                                    :class="{ 'border-red-500': editForm.errors.email }"
                                    required
                                />
                                <p v-if="editForm.errors.email" class="mt-1 text-xs text-red-500">{{ editForm.errors.email }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="edit-username">Username *</Label>
                                <Input
                                    id="edit-username"
                                    autocomplete="username"
                                    v-model="editForm.username"
                                    :class="{ 'border-red-500': editForm.errors.username }"
                                    required
                                />
                                <p v-if="editForm.errors.username" class="mt-1 text-xs text-red-500">{{ editForm.errors.username }}</p>
                            </div>
                            <div>
                                <Label for="edit-password">Password Baru (kosongkan jika tidak diubah)</Label>
                                <Input
                                    id="edit-password"
                                    type="password"
                                    autocomplete="new-password"
                                    v-model="editForm.password"
                                    :class="{ 'border-red-500': editForm.errors.password }"
                                />
                                <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-500">{{ editForm.errors.password }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Information -->
                    <div>
                        <h3 class="text-sm font-medium mb-3 flex items-center">
                            <Building2 class="h-4 w-4 mr-2" />
                            Informasi Karyawan
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label for="edit-nik">NIK *</Label>
                                <Input
                                    id="edit-nik"
                                    v-model="editForm.nik"
                                    :class="{ 'border-red-500': editForm.errors.nik }"
                                    required
                                />
                                <p v-if="editForm.errors.nik" class="mt-1 text-xs text-red-500">{{ editForm.errors.nik }}</p>
                            </div>
                            <div>
                                <Label for="edit-position">Jabatan *</Label>
                                <Input
                                    id="edit-position"
                                    v-model="editForm.position"
                                    :class="{ 'border-red-500': editForm.errors.position }"
                                    required
                                />
                                <p v-if="editForm.errors.position" class="mt-1 text-xs text-red-500">{{ editForm.errors.position }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="edit-department">Departemen *</Label>
                                <Input
                                    id="edit-department"
                                    v-model="editForm.department"
                                    :class="{ 'border-red-500': editForm.errors.department }"
                                    required
                                />
                                <p v-if="editForm.errors.department" class="mt-1 text-xs text-red-500">{{ editForm.errors.department }}</p>
                            </div>
                            <div>
                                <Label for="edit-hire-date">Tanggal Bergabung *</Label>
                                <Input
                                    id="edit-hire-date"
                                    type="date"
                                    v-model="editForm.hire_date"
                                    :class="{ 'border-red-500': editForm.errors.hire_date }"
                                    required
                                />
                                <p v-if="editForm.errors.hire_date" class="mt-1 text-xs text-red-500">{{ editForm.errors.hire_date }}</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="edit-phone">Telepon</Label>
                                <Input
                                    id="edit-phone"
                                    v-model="editForm.phone"
                                    :class="{ 'border-red-500': editForm.errors.phone }"
                                />
                                <p v-if="editForm.errors.phone" class="mt-1 text-xs text-red-500">{{ editForm.errors.phone }}</p>
                            </div>
                            <div>
                                <Label for="edit-salary">Gaji Pokok</Label>
                                <Input
                                    id="edit-salary"
                                    type="number"
                                    v-model="editForm.salary"
                                    :class="{ 'border-red-500': editForm.errors.salary }"
                                />
                                <p v-if="editForm.errors.salary" class="mt-1 text-xs text-red-500">{{ editForm.errors.salary }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <Label for="edit-address">Alamat</Label>
                            <Input
                                id="edit-address"
                                v-model="editForm.address"
                                :class="{ 'border-red-500': editForm.errors.address }"
                            />
                            <p v-if="editForm.errors.address" class="mt-1 text-xs text-red-500">{{ editForm.errors.address }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <Label for="edit-status">Status *</Label>
                                <select
                                    id="edit-status"
                                    v-model="editForm.status"
                                    class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none dark:bg-gray-800 dark:text-white"
                                    required
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                    <option value="terminated">Berhenti</option>
                                </select>
                                <p v-if="editForm.errors.status" class="mt-1 text-xs text-red-500">{{ editForm.errors.status }}</p>
                            </div>
                            <div>
                                <Label for="edit-password-confirmation">Konfirmasi Password</Label>
                                <Input
                                    id="edit-password-confirmation"
                                    type="password"
                                    autocomplete="new-password"
                                    v-model="editForm.password_confirmation"
                                />
                            </div>
                        </div>
                        <div class="mt-4">
                            <Label for="edit-notes">Catatan</Label>
                            <textarea
                                id="edit-notes"
                                v-model="editForm.notes"
                                class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                :class="{ 'border-red-500': editForm.errors.notes }"
                            ></textarea>
                            <p v-if="editForm.errors.notes" class="mt-1 text-xs text-red-500">{{ editForm.errors.notes }}</p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Karyawan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Employee Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-red-600">Konfirmasi Penghapusan</h2>
                    <button @click="showDeleteModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        fill-rule="evenodd"
                                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">Peringatan: Tindakan ini tidak dapat dibatalkan</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    <p>
                                        Anda akan menghapus secara permanen <strong>{{ employeeToDelete?.user.name }}</strong>.
                                    </p>
                                    <div class="mt-3 space-y-1">
                                        <p><strong>Ini juga akan menghapus:</strong></p>
                                        <ul class="ml-2 list-inside list-disc space-y-1">
                                            <li>Akun pengguna terkait</li>
                                            <li>Semua data karyawan secara permanen</li>
                                            <li>Riwayat dan catatan terkait</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Karyawan:</strong> {{ employeeToDelete?.user.name }}<br />
                            <strong>NIK:</strong> {{ employeeToDelete?.nik }}<br />
                            <strong>Email:</strong> {{ employeeToDelete?.user.email }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer">Batal</Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        Ya, Hapus Karyawan
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>