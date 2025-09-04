<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import { Switch } from '@/components/ui/switch';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Building2, ArrowLeft } from 'lucide-vue-next';

interface BankForm {
  bank_name: string;
  bank_code: string;
  account_number: string;
  account_name: string;
  branch: string;
  swift_code: string;
  description: string;
  is_active: boolean;
  admin_fee: number;
  bank_type: 'local' | 'international';
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Bank Management', href: '/admin/banks' },
  { title: 'Tambah Bank', href: '/admin/banks/create' },
];

const form = useForm<BankForm>({
  bank_name: '',
  bank_code: '',
  account_number: '',
  account_name: '',
  branch: '',
  swift_code: '',
  description: '',
  is_active: true,
  admin_fee: 0,
  bank_type: 'local',
});

const submit = () => {
  form.post('/admin/banks', {
    onSuccess: () => {
      // Redirect will be handled by the controller
    },
  });
};
</script>

<template>
  <Head title="Tambah Bank" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="ghost" size="sm" asChild>
          <Link href="/admin/banks">
            <ArrowLeft class="h-4 w-4" />
          </Link>
        </Button>
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Tambah Bank</h1>
          <p class="text-muted-foreground">
            Tambahkan bank pembayaran baru ke sistem
          </p>
        </div>
      </div>

      <!-- Form -->
      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Building2 class="h-5 w-5" />
            Informasi Bank
          </CardTitle>
          <CardDescription>
            Masukkan detail bank pembayaran
          </CardDescription>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Bank Name -->
              <div class="space-y-2">
                <Label for="bank_name">Nama Bank *</Label>
                <Input
                  id="bank_name"
                  v-model="form.bank_name"
                  placeholder="Contoh: Bank Central Asia"
                  :class="{ 'border-red-500': form.errors.bank_name }"
                />
                <p v-if="form.errors.bank_name" class="text-sm text-red-600">
                  {{ form.errors.bank_name }}
                </p>
              </div>

              <!-- Bank Code -->
              <div class="space-y-2">
                <Label for="bank_code">Kode Bank *</Label>
                <Input
                  id="bank_code"
                  v-model="form.bank_code"
                  placeholder="Contoh: BCA"
                  :class="{ 'border-red-500': form.errors.bank_code }"
                />
                <p v-if="form.errors.bank_code" class="text-sm text-red-600">
                  {{ form.errors.bank_code }}
                </p>
              </div>

              <!-- Account Number -->
              <div class="space-y-2">
                <Label for="account_number">Nomor Rekening *</Label>
                <Input
                  id="account_number"
                  v-model="form.account_number"
                  placeholder="Contoh: 1234567890"
                  :class="{ 'border-red-500': form.errors.account_number }"
                />
                <p v-if="form.errors.account_number" class="text-sm text-red-600">
                  {{ form.errors.account_number }}
                </p>
              </div>

              <!-- Account Name -->
              <div class="space-y-2">
                <Label for="account_name">Nama Pemilik Rekening *</Label>
                <Input
                  id="account_name"
                  v-model="form.account_name"
                  placeholder="Contoh: PT. Contoh Indonesia"
                  :class="{ 'border-red-500': form.errors.account_name }"
                />
                <p v-if="form.errors.account_name" class="text-sm text-red-600">
                  {{ form.errors.account_name }}
                </p>
              </div>

              <!-- Branch -->
              <div class="space-y-2">
                <Label for="branch">Cabang</Label>
                <Input
                  id="branch"
                  v-model="form.branch"
                  placeholder="Contoh: Jakarta Pusat"
                  :class="{ 'border-red-500': form.errors.branch }"
                />
                <p v-if="form.errors.branch" class="text-sm text-red-600">
                  {{ form.errors.branch }}
                </p>
              </div>

              <!-- SWIFT Code -->
              <div class="space-y-2">
                <Label for="swift_code">Kode SWIFT</Label>
                <Input
                  id="swift_code"
                  v-model="form.swift_code"
                  placeholder="Contoh: CENAIDJA"
                  :class="{ 'border-red-500': form.errors.swift_code }"
                />
                <p v-if="form.errors.swift_code" class="text-sm text-red-600">
                  {{ form.errors.swift_code }}
                </p>
              </div>

              <!-- Bank Type -->
              <div class="space-y-2">
                <Label for="bank_type">Tipe Bank *</Label>
                <select 
                  v-model="form.bank_type"
                  id="bank_type"
                  :class="['flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', { 'border-red-500': form.errors.bank_type }]"
                >
                  <option value="" disabled>Pilih tipe bank</option>
                  <option value="local">Lokal</option>
                  <option value="international">Internasional</option>
                </select>
                <p v-if="form.errors.bank_type" class="text-sm text-red-600">
                  {{ form.errors.bank_type }}
                </p>
              </div>

              <!-- Admin Fee -->
              <div class="space-y-2">
                <Label for="admin_fee">Biaya Admin</Label>
                <Input
                  id="admin_fee"
                  v-model.number="form.admin_fee"
                  type="number"
                  min="0"
                  step="0.01"
                  placeholder="0"
                  :class="{ 'border-red-500': form.errors.admin_fee }"
                />
                <p v-if="form.errors.admin_fee" class="text-sm text-red-600">
                  {{ form.errors.admin_fee }}
                </p>
              </div>
            </div>

            <!-- Description -->
            <div class="space-y-2">
              <Label for="description">Deskripsi</Label>
              <textarea
                id="description"
                v-model="form.description"
                placeholder="Deskripsi tambahan tentang bank ini..."
                rows="3"
                :class="['flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50', { 'border-red-500': form.errors.description }]"
              />
              <p v-if="form.errors.description" class="text-sm text-red-600">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Active Status -->
            <div class="flex items-center space-x-2">
              <Switch
                id="is_active"
                v-model:checked="form.is_active"
              />
              <Label for="is_active">Bank Aktif</Label>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center gap-4 pt-4">
              <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Menyimpan...' : 'Simpan Bank' }}
              </Button>
              <Button variant="outline" type="button" asChild>
                <Link href="/admin/banks">Batal</Link>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>