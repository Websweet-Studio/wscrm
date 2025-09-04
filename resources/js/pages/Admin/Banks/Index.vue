<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Building2, Plus, Edit, Eye, Trash2, ToggleLeft, ToggleRight } from 'lucide-vue-next';
import { formatPrice } from '@/lib/utils';

interface Bank {
  id: number;
  bank_name: string;
  bank_code: string;
  account_number: string;
  account_name: string;
  branch?: string;
  swift_code?: string;
  description?: string;
  is_active: boolean;
  admin_fee: number;
  bank_type: 'local' | 'international';
  created_at: string;
  updated_at: string;
}

interface PaginatedBanks {
  data: Bank[];
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
  links: Array<{
    url: string | null;
    label: string;
    active: boolean;
  }>;
}

interface Props {
  banks: PaginatedBanks;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Bank Management', href: '/admin/banks' },
];

const getStatusClass = (isActive: boolean) => {
  return isActive
    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
};

const getBankTypeClass = (type: string) => {
  return type === 'local'
    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'
    : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200';
};

const toggleBankStatus = (bank: Bank) => {
  router.patch(`/admin/banks/${bank.id}/toggle-status`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Success message will be handled by flash messages
    },
  });
};

const deleteBank = (bank: Bank) => {
  if (confirm(`Apakah Anda yakin ingin menghapus bank ${bank.bank_name}?`)) {
    router.delete(`/admin/banks/${bank.id}`, {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Bank Management" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Bank Management</h1>
          <p class="text-muted-foreground">
            Kelola bank pembayaran untuk sistem invoice
          </p>
        </div>
        <Button asChild>
          <Link href="/admin/banks/create">
            <Plus class="h-4 w-4 mr-2" />
            Tambah Bank
          </Link>
        </Button>
      </div>

      <!-- Banks Table -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Building2 class="h-5 w-5" />
            Daftar Bank
          </CardTitle>
          <CardDescription>
            Total {{ banks.total }} bank terdaftar
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="banks.data.length > 0">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Bank</TableHead>
                  <TableHead>Kode</TableHead>
                  <TableHead>Rekening</TableHead>
                  <TableHead>Tipe</TableHead>
                  <TableHead>Admin Fee</TableHead>
                  <TableHead>Status</TableHead>
                  <TableHead class="text-right">Aksi</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-for="bank in banks.data" :key="bank.id">
                  <TableCell>
                    <div>
                      <div class="font-medium">{{ bank.bank_name }}</div>
                      <div class="text-sm text-muted-foreground">{{ bank.account_name }}</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <code class="bg-muted px-2 py-1 rounded text-sm">{{ bank.bank_code }}</code>
                  </TableCell>
                  <TableCell>
                    <div>
                      <div class="font-mono text-sm">{{ bank.account_number }}</div>
                      <div v-if="bank.branch" class="text-sm text-muted-foreground">{{ bank.branch }}</div>
                    </div>
                  </TableCell>
                  <TableCell>
                    <Badge :class="getBankTypeClass(bank.bank_type)">
                      {{ bank.bank_type === 'local' ? 'Lokal' : 'Internasional' }}
                    </Badge>
                  </TableCell>
                  <TableCell>
                    {{ formatPrice(bank.admin_fee) }}
                  </TableCell>
                  <TableCell>
                    <Badge :class="getStatusClass(bank.is_active)">
                      {{ bank.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                  </TableCell>
                  <TableCell class="text-right">
                    <div class="flex items-center justify-end gap-2">
                      <Button variant="ghost" size="sm" asChild>
                        <Link :href="`/admin/banks/${bank.id}`">
                          <Eye class="h-4 w-4" />
                        </Link>
                      </Button>
                      <Button variant="ghost" size="sm" asChild>
                        <Link :href="`/admin/banks/${bank.id}/edit`">
                          <Edit class="h-4 w-4" />
                        </Link>
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="toggleBankStatus(bank)"
                        :title="bank.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                      >
                        <ToggleRight v-if="bank.is_active" class="h-4 w-4 text-green-600" />
                        <ToggleLeft v-else class="h-4 w-4 text-red-600" />
                      </Button>
                      <Button 
                        variant="ghost" 
                        size="sm" 
                        @click="deleteBank(bank)"
                        class="text-red-600 hover:text-red-700"
                      >
                        <Trash2 class="h-4 w-4" />
                      </Button>
                    </div>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>

            <!-- Pagination -->
            <div v-if="banks.last_page > 1" class="flex items-center justify-center space-x-2 mt-4">
              <Button 
                v-for="link in banks.links" 
                :key="link.label"
                variant="ghost" 
                size="sm"
                :class="{
                  'bg-primary text-primary-foreground': link.active,
                  'pointer-events-none opacity-50': !link.url
                }"
                @click="link.url && router.get(link.url)"
                v-html="link.label"
              />
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <Building2 class="mx-auto h-12 w-12 text-muted-foreground/40" />
            <h3 class="mt-2 text-sm font-semibold">Belum ada bank</h3>
            <p class="mt-1 text-sm text-muted-foreground">
              Mulai dengan menambahkan bank pembayaran pertama.
            </p>
            <div class="mt-6">
              <Button asChild>
                <Link href="/admin/banks/create">
                  <Plus class="h-4 w-4 mr-2" />
                  Tambah Bank
                </Link>
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>