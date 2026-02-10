<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ChevronDown, ChevronUp, Clock, Edit, Key, LogIn, Mail, MoreHorizontal, Plus, Search, Trash2, UserCheck, Users, UserX, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

interface Customer {
    id: number;
    name: string;
    email: string;
    username?: string;
    phone?: string;
    address?: string;
    city?: string;
    country?: string;
    postal_code?: string;
    status: 'active' | 'inactive' | 'suspended';
    created_at: string;
    orders_count: number;
    services_count: number;
}

interface Props {
    customers?: {
        data: Customer[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: any[];
    };
    filters?: {
        search?: string;
        status?: string;
    };
    sort?: string;
    direction?: string;
}

const props = defineProps<Props>();

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedCustomer = ref<Customer | null>(null);
const customerToDelete = ref<Customer | null>(null);

// Username checking state
const usernameStatus = ref<'idle' | 'checking' | 'available' | 'unavailable'>('idle');
const usernameMessage = ref('');
let usernameDebounceTimer: number;

const createForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    city: '',
    country: '',
    postal_code: '',
});

const editForm = useForm({
    name: '',
    email: '',
    username: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    city: '',
    country: '',
    postal_code: '',
    status: 'active' as 'active' | 'inactive' | 'suspended',
});

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
    { title: 'Pelanggan', href: '/admin/customers' },
];

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const handleSearch = () => {
    router.get(
        '/admin/customers',
        {
            search: search.value,
            status: status.value,
            sort: props.sort,
            direction: props.direction,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const sendWelcomeEmail = (customer: Customer) => {
    if (confirm(`Kirim welcome email ke ${customer.name}?`)) {
        router.post(
            `/admin/customers/${customer.id}/welcome-email`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Handled by flash message
                },
            },
        );
    }
};

const resendNewPassword = (customer: Customer) => {
    if (confirm(`Reset password untuk ${customer.name} dan kirim via email?`)) {
        router.post(
            `/admin/customers/${customer.id}/resend-password`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    // Handled by flash message
                },
            },
        );
    }
};

const getStatusClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
        case 'suspended':
            return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
        case 'inactive':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    }
};

const submitCreate = () => {
    console.log('Submitting create form...', createForm.data());

    // Get fresh CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    console.log('Using CSRF token:', csrfToken);

    createForm
        .transform((data) => ({
            ...data,
            _token: csrfToken,
        }))
        .post('/admin/customers', {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            onSuccess: (page) => {
                console.log('Customer created successfully');
                showCreateModal.value = false;
                createForm.reset();
            },
            onError: (errors) => {
                console.error('Create customer error:', errors);
                // If CSRF error, reload page
                if (errors[419] || Object.values(errors).some((e) => String(e).includes('419'))) {
                    console.warn('CSRF error detected, reloading...');
                    window.location.reload();
                }
            },
            onFinish: () => {
                console.log('Create request finished');
            },
        });
};

const openEditModal = (customer: Customer) => {
    selectedCustomer.value = customer;
    editForm.reset();
    editForm.name = customer.name;
    editForm.email = customer.email;
    editForm.username = customer.username || '';
    editForm.phone = customer.phone || '';
    editForm.address = customer.address || '';
    editForm.city = customer.city || '';
    editForm.country = customer.country || '';
    editForm.postal_code = customer.postal_code || '';
    editForm.status = customer.status;
    // Clear password fields explicitly when editing
    editForm.password = '';
    editForm.password_confirmation = '';
    // Reset username status
    usernameStatus.value = 'idle';
    usernameMessage.value = '';
    showEditModal.value = true;
};

// Username availability checking
const checkUsernameAvailability = async (username: string, excludeId?: number) => {
    if (!username || username.length < 3) {
        usernameStatus.value = 'idle';
        usernameMessage.value = '';
        return;
    }

    usernameStatus.value = 'checking';
    usernameMessage.value = 'Mengecek ketersediaan...';

    try {
        const params = new URLSearchParams({ username });
        if (excludeId) {
            params.append('exclude_id', excludeId.toString());
        }

        const response = await fetch(`/api/customer/username/check?${params}`);
        const data = await response.json();

        usernameStatus.value = data.available ? 'available' : 'unavailable';
        usernameMessage.value = data.message;
    } catch (error) {
        console.error('Error checking username availability:', error);
        usernameStatus.value = 'idle';
        usernameMessage.value = '';
    }
};

// Watch for username changes in edit form
watch(
    () => editForm.username,
    (newValue) => {
        clearTimeout(usernameDebounceTimer);
        if (newValue && newValue.length >= 3) {
            usernameDebounceTimer = setTimeout(() => {
                checkUsernameAvailability(newValue, selectedCustomer.value?.id);
            }, 500);
        } else {
            usernameStatus.value = 'idle';
            usernameMessage.value = '';
        }
    },
);

const submitEdit = () => {
    if (!selectedCustomer.value) return;

    console.log('Submitting edit form...', editForm.data());
    console.log('Selected customer ID:', selectedCustomer.value.id);

    // Transform data to remove empty password fields
    const formData = editForm.transform((data) => {
        const result = { ...data };
        // Remove password fields if password is empty
        if (!data.password) {
            delete result.password;
            delete result.password_confirmation;
        }
        console.log('Transformed data:', result);
        return result;
    });

    formData.put(`/admin/customers/${selectedCustomer.value.id}`, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Customer updated successfully', page);
            showEditModal.value = false;
            editForm.reset();
            selectedCustomer.value = null;
            // Force reload to show updated data
            router.reload({ only: ['customers'] });
        },
        onError: (errors) => {
            console.error('Update customer error:', errors);
            // Show detailed error info
            if (typeof errors === 'object') {
                Object.keys(errors).forEach((key) => {
                    console.error(`${key}: ${errors[key]}`);
                });
            }
        },
        onFinish: () => {
            console.log('Update request finished');
        },
    });
};

const openDeleteModal = (customer: Customer) => {
    customerToDelete.value = customer;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!customerToDelete.value) return;

    router.delete(`/admin/customers/${customerToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            customerToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete customer error:', errors);
        },
    });
};

const impersonateCustomer = (customer: Customer) => {
    if (confirm(`Login sebagai ${customer.name}?`)) {
        router.post(
            `/admin/impersonate/${customer.id}`,
            {},
            {
                onSuccess: () => {
                    // Redirect akan ditangani oleh controller
                },
                onError: (errors) => {
                    console.error('Impersonation error:', errors);
                    alert('Gagal login sebagai customer. Silakan coba lagi.');
                },
            },
        );
    }
};

const sortBy = (field: string) => {
    const params = new URLSearchParams();

    // Preserve existing filters
    if (search.value) params.set('search', search.value);
    if (status.value) params.set('status', status.value);

    // Handle sorting
    let direction = 'asc';
    if (props.sort === field && props.direction === 'asc') {
        direction = 'desc';
    }

    params.set('sort', field);
    params.set('direction', direction);

    const url = `/admin/customers${params.toString() ? '?' + params.toString() : ''}`;
    router.get(url, {}, { preserveState: true });
};

const getSortIcon = (field: string) => {
    if (props.sort !== field) return null;
    return props.direction === 'asc' ? ChevronUp : ChevronDown;
};
</script>

<template>
    <Head title="Admin - Kelola Pelanggan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="w-full max-w-none space-y-4 sm:space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">Kelola Pelanggan</h1>
                    <p class="text-sm text-muted-foreground sm:text-base">Kelola akun dan informasi pelanggan</p>
                </div>
                <Button @click="showCreateModal = true" class="w-full cursor-pointer sm:w-auto">
                    <Plus class="mr-2 h-4 w-4" />
                    <span class="hidden sm:inline">Tambah Pelanggan</span>
                    <span class="sm:hidden">Tambah</span>
                </Button>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium sm:text-sm">Total Pelanggan</CardTitle>
                        <Users class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-bold sm:text-2xl">{{ customers?.total || 0 }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium sm:text-sm">Aktif</CardTitle>
                        <UserCheck class="h-3 w-3 text-green-600 sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-bold text-green-600 sm:text-2xl">
                            {{ customers?.data?.filter((c) => c.status === 'active').length || 0 }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium sm:text-sm">Ditangguhkan</CardTitle>
                        <UserX class="h-3 w-3 text-red-600 sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-bold text-red-600 sm:text-2xl">
                            {{ customers?.data?.filter((c) => c.status === 'suspended').length || 0 }}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-xs font-medium sm:text-sm">Tidak Aktif</CardTitle>
                        <Clock class="h-3 w-3 text-muted-foreground sm:h-4 sm:w-4" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-xl font-bold text-muted-foreground sm:text-2xl">
                            {{ customers?.data?.filter((c) => c.status === 'inactive').length || 0 }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Customer List -->
            <Card>
                <CardHeader class="pb-4">
                    <CardTitle class="text-lg sm:text-xl">Pelanggan</CardTitle>
                    <CardDescription class="text-sm">Kelola akun dan informasi pelanggan</CardDescription>
                </CardHeader>
                <CardContent class="px-3 sm:px-4 lg:px-6">
                    <!-- Search and Filter -->
                    <div class="mb-4 flex flex-col gap-3 sm:mb-6 sm:flex-row sm:gap-4">
                        <div class="relative max-w-none flex-1 sm:max-w-sm">
                            <Search class="absolute top-2.5 left-2.5 h-4 w-4 text-muted-foreground" />
                            <Input v-model="search" placeholder="Cari pelanggan..." class="pl-8" @keyup.enter="handleSearch" />
                        </div>
                        <select
                            v-model="status"
                            class="flex h-9 w-full cursor-pointer rounded-md border border-input bg-background px-3 py-1 text-sm text-foreground shadow-sm transition-colors placeholder:text-muted-foreground focus-visible:ring-1 focus-visible:ring-ring focus-visible:outline-none sm:w-[180px] dark:bg-gray-800 dark:text-white"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak Aktif</option>
                            <option value="suspended">Ditangguhkan</option>
                        </select>
                        <Button @click="handleSearch" class="cursor-pointer">Cari</Button>
                    </div>

                    <!-- Customer Table -->
                    <div v-if="!customers?.data || customers.data.length === 0" class="py-12 text-center text-muted-foreground">
                        <Users class="mx-auto h-12 w-12 text-muted-foreground/40" />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">Pelanggan tidak ditemukan</h3>
                        <p class="mt-1 text-sm text-muted-foreground">Coba sesuaikan kriteria pencarian Anda.</p>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full border-collapse">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('id')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>ID</span>
                                            <component :is="getSortIcon('id')" v-if="getSortIcon('id')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('name')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Nama</span>
                                            <component :is="getSortIcon('name')" v-if="getSortIcon('name')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('email')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Email</span>
                                            <component :is="getSortIcon('email')" v-if="getSortIcon('email')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('username')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Username</span>
                                            <component :is="getSortIcon('username')" v-if="getSortIcon('username')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('phone')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Telepon</span>
                                            <component :is="getSortIcon('phone')" v-if="getSortIcon('phone')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('city')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Kota</span>
                                            <component :is="getSortIcon('city')" v-if="getSortIcon('city')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('status')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Status</span>
                                            <component :is="getSortIcon('status')" v-if="getSortIcon('status')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('orders_count')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Pesanan</span>
                                            <component :is="getSortIcon('orders_count')" v-if="getSortIcon('orders_count')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">Layanan</th>
                                    <th class="px-3 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground">
                                        <button @click="sortBy('created_at')" class="flex cursor-pointer items-center space-x-1 hover:text-primary">
                                            <span>Bergabung</span>
                                            <component :is="getSortIcon('created_at')" v-if="getSortIcon('created_at')" class="h-3 w-3" />
                                        </button>
                                    </th>
                                    <th class="px-3 py-3 text-center text-xs font-medium tracking-wider text-muted-foreground">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="customer in customers.data"
                                    :key="customer.id"
                                    class="border-b border-border transition-colors hover:bg-muted/30"
                                >
                                    <td class="px-3 py-4 text-sm font-medium text-foreground">#{{ customer.id }}</td>
                                    <td class="px-3 py-4 text-sm font-medium text-foreground">{{ customer.name }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ customer.email }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ customer.username || '-' }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ customer.phone || '-' }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ customer.city || '-' }}</td>
                                    <td class="px-3 py-4">
                                        <span
                                            :class="`inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ${getStatusClass(customer.status)}`"
                                        >
                                            {{ customer.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-4 text-center text-sm font-medium text-foreground">{{ customer.orders_count }}</td>
                                    <td class="px-3 py-4 text-center text-sm font-medium text-foreground">{{ customer.services_count }}</td>
                                    <td class="px-3 py-4 text-sm text-muted-foreground">{{ formatDate(customer.created_at) }}</td>
                                    <td class="px-3 py-4">
                                        <div class="flex items-center justify-end">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger as-child>
                                                    <Button variant="ghost" class="h-8 w-8 p-0 cursor-pointer">
                                                        <span class="sr-only">Open menu</span>
                                                        <MoreHorizontal class="h-4 w-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>Aksi</DropdownMenuLabel>
                                                    <DropdownMenuItem @click="router.visit(`/admin/customers/${customer.id}`)" class="cursor-pointer">
                                                        <Users class="mr-2 h-4 w-4" />
                                                        Lihat Detail
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="openEditModal(customer)" class="cursor-pointer">
                                                        <Edit class="mr-2 h-4 w-4" />
                                                        Edit
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="impersonateCustomer(customer)" class="cursor-pointer">
                                                        <LogIn class="mr-2 h-4 w-4" />
                                                        Login sebagai User
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="sendWelcomeEmail(customer)" class="cursor-pointer">
                                                        <Mail class="mr-2 h-4 w-4" />
                                                        Kirim Welcome Email
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem @click="resendNewPassword(customer)" class="cursor-pointer">
                                                        <Key class="mr-2 h-4 w-4" />
                                                        Resend New Password
                                                    </DropdownMenuItem>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem @click="openDeleteModal(customer)" class="cursor-pointer text-red-600 focus:text-red-600">
                                                        <Trash2 class="mr-2 h-4 w-4" />
                                                        Hapus
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="customers?.links && customers.links.length > 3" class="flex items-center justify-between border-t pt-6">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ (customers.current_page - 1) * customers.per_page + 1 || 0 }} to
                            {{ Math.min(customers.current_page * customers.per_page, customers.total) || 0 }} of {{ customers.total || 0 }} results
                        </div>
                        <div class="flex items-center gap-1">
                            <template v-for="link in customers.links" :key="link.label">
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

        <!-- Create Customer Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showCreateModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Tambah Pelanggan Baru</h2>
                    <button @click="showCreateModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitCreate" class="space-y-4">
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

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-password">Kata Sandi *</Label>
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
                        <div>
                            <Label for="create-password-confirmation">Konfirmasi Kata Sandi *</Label>
                            <Input
                                id="create-password-confirmation"
                                type="password"
                                autocomplete="new-password"
                                v-model="createForm.password_confirmation"
                                required
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-phone">Telepon</Label>
                            <Input id="create-phone" v-model="createForm.phone" :class="{ 'border-red-500': createForm.errors.phone }" />
                            <p v-if="createForm.errors.phone" class="mt-1 text-xs text-red-500">{{ createForm.errors.phone }}</p>
                        </div>
                        <div>
                            <Label for="create-city">Kota</Label>
                            <Input id="create-city" v-model="createForm.city" :class="{ 'border-red-500': createForm.errors.city }" />
                            <p v-if="createForm.errors.city" class="mt-1 text-xs text-red-500">{{ createForm.errors.city }}</p>
                        </div>
                    </div>

                    <div>
                        <Label for="create-address">Alamat</Label>
                        <Input id="create-address" v-model="createForm.address" :class="{ 'border-red-500': createForm.errors.address }" />
                        <p v-if="createForm.errors.address" class="mt-1 text-xs text-red-500">{{ createForm.errors.address }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="create-country">Negara</Label>
                            <Input id="create-country" v-model="createForm.country" :class="{ 'border-red-500': createForm.errors.country }" />
                            <p v-if="createForm.errors.country" class="mt-1 text-xs text-red-500">{{ createForm.errors.country }}</p>
                        </div>
                        <div>
                            <Label for="create-postal-code">Kode Pos</Label>
                            <Input
                                id="create-postal-code"
                                v-model="createForm.postal_code"
                                :class="{ 'border-red-500': createForm.errors.postal_code }"
                            />
                            <p v-if="createForm.errors.postal_code" class="mt-1 text-xs text-red-500">{{ createForm.errors.postal_code }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showCreateModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="createForm.processing">
                            {{ createForm.processing ? 'Membuat...' : 'Buat Pelanggan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Customer Modal -->
        <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showEditModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Edit Pelanggan</h2>
                    <button @click="showEditModal = false" class="cursor-pointer text-gray-500 hover:text-gray-700">
                        <X class="h-4 w-4" />
                    </button>
                </div>
                <form @submit.prevent="submitEdit" class="space-y-4">
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
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-username">Username</Label>
                            <div class="relative">
                                <Input
                                    id="edit-username"
                                    type="text"
                                    autocomplete="username"
                                    v-model="editForm.username"
                                    :class="{
                                        'border-red-500': editForm.errors.username || usernameStatus === 'unavailable',
                                        'border-green-500': usernameStatus === 'available',
                                        'border-yellow-500': usernameStatus === 'checking',
                                    }"
                                    placeholder="Username (opsional, minimal 5 karakter)"
                                />
                                <div v-if="usernameStatus === 'checking'" class="absolute top-1/2 right-3 -translate-y-1/2 transform">
                                    <div class="h-4 w-4 animate-spin rounded-full border-2 border-blue-600 border-t-transparent"></div>
                                </div>
                                <div v-else-if="usernameStatus === 'available'" class="absolute top-1/2 right-3 -translate-y-1/2 transform">
                                    <svg class="h-4 w-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div v-else-if="usernameStatus === 'unavailable'" class="absolute top-1/2 right-3 -translate-y-1/2 transform">
                                    <svg class="h-4 w-4 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd"
                                        />
                                    </svg>
                                </div>
                            </div>
                            <p v-if="editForm.errors.username" class="mt-1 text-xs text-red-500">{{ editForm.errors.username }}</p>
                            <p
                                v-else-if="usernameMessage"
                                class="mt-1 text-xs"
                                :class="{
                                    'text-green-600': usernameStatus === 'available',
                                    'text-red-600': usernameStatus === 'unavailable',
                                    'text-blue-600': usernameStatus === 'checking',
                                }"
                            >
                                {{ usernameMessage }}
                            </p>
                        </div>
                        <div>
                            <!-- Kosong untuk spacing -->
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-password">Kata Sandi Baru</Label>
                            <Input
                                id="edit-password"
                                type="password"
                                autocomplete="new-password"
                                v-model="editForm.password"
                                :class="{ 'border-red-500': editForm.errors.password }"
                            />
                            <p v-if="editForm.errors.password" class="mt-1 text-xs text-red-500">{{ editForm.errors.password }}</p>
                        </div>
                        <div>
                            <Label for="edit-password-confirmation">Konfirmasi Kata Sandi</Label>
                            <Input
                                id="edit-password-confirmation"
                                type="password"
                                autocomplete="new-password"
                                v-model="editForm.password_confirmation"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <Label for="edit-phone">Telepon</Label>
                            <Input id="edit-phone" v-model="editForm.phone" :class="{ 'border-red-500': editForm.errors.phone }" />
                            <p v-if="editForm.errors.phone" class="mt-1 text-xs text-red-500">{{ editForm.errors.phone }}</p>
                        </div>
                        <div>
                            <Label for="edit-city">Kota</Label>
                            <Input id="edit-city" v-model="editForm.city" :class="{ 'border-red-500': editForm.errors.city }" />
                            <p v-if="editForm.errors.city" class="mt-1 text-xs text-red-500">{{ editForm.errors.city }}</p>
                        </div>
                    </div>

                    <div>
                        <Label for="edit-address">Alamat</Label>
                        <Input id="edit-address" v-model="editForm.address" :class="{ 'border-red-500': editForm.errors.address }" />
                        <p v-if="editForm.errors.address" class="mt-1 text-xs text-red-500">{{ editForm.errors.address }}</p>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <Label for="edit-country">Negara</Label>
                            <Input id="edit-country" v-model="editForm.country" :class="{ 'border-red-500': editForm.errors.country }" />
                            <p v-if="editForm.errors.country" class="mt-1 text-xs text-red-500">{{ editForm.errors.country }}</p>
                        </div>
                        <div>
                            <Label for="edit-postal-code">Kode Pos</Label>
                            <Input id="edit-postal-code" v-model="editForm.postal_code" :class="{ 'border-red-500': editForm.errors.postal_code }" />
                            <p v-if="editForm.errors.postal_code" class="mt-1 text-xs text-red-500">{{ editForm.errors.postal_code }}</p>
                        </div>
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
                                <option value="suspended">Ditangguhkan</option>
                            </select>
                            <p v-if="editForm.errors.status" class="mt-1 text-xs text-red-500">{{ editForm.errors.status }}</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="mt-6 flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="showEditModal = false" class="cursor-pointer"> Batal </Button>
                        <Button type="submit" :disabled="editForm.processing">
                            {{ editForm.processing ? 'Memperbarui...' : 'Perbarui Pelanggan' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Customer Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/50" @click="showDeleteModal = false"></div>

            <!-- Modal Content -->
            <div class="relative mx-4 w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-gray-900">
                <!-- Header -->
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
                                        Anda akan menghapus secara permanen <strong>{{ customerToDelete?.name }}</strong
                                        >.
                                    </p>
                                    <div class="mt-3 space-y-1">
                                        <p><strong>Ini juga akan menghapus:</strong></p>
                                        <ul class="ml-2 list-inside list-disc space-y-1">
                                            <li>{{ customerToDelete?.orders_count || 0 }} pesanan</li>
                                            <li>{{ customerToDelete?.services_count || 0 }} layanan</li>
                                            <li>Semua faktur terkait</li>
                                            <li>Semua data terkait secara permanen</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>Pelanggan:</strong> {{ customerToDelete?.name }}<br />
                            <strong>Email:</strong> {{ customerToDelete?.email }}<br />
                            <strong>ID:</strong> #{{ customerToDelete?.id }}
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-6 flex justify-end gap-2">
                    <Button type="button" variant="outline" @click="showDeleteModal = false" class="cursor-pointer"> Batal </Button>
                    <Button type="button" class="cursor-pointer bg-red-600 text-white hover:bg-red-700" @click="confirmDelete">
                        Ya, Hapus Pelanggan
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
