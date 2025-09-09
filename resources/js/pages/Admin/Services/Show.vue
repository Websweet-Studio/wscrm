<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Mail, Phone, MapPin, Calendar, Settings, Server, Globe, Clock } from 'lucide-vue-next';

interface Customer {
  id: number;
  name: string;
  email: string;
  phone?: string;
  address?: string;
  city?: string;
  country?: string;
}

interface HostingPlan {
  id: number;
  plan_name: string;
  storage_gb: number;
  cpu_cores: number;
  ram_gb: number;
  bandwidth: string;
  features: string[];
}

interface Service {
  id: number;
  service_type: 'hosting' | 'domain';
  domain_name: string;
  status: 'active' | 'suspended' | 'terminated' | 'pending';
  expires_at: string;
  auto_renew: boolean;
  created_at: string;
  updated_at: string;
  customer: Customer;
  hosting_plan?: HostingPlan;
  metadata?: any;
}

interface Props {
  service: Service;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Layanan', href: '/admin/services' },
  { title: props.service.domain_name, href: `/admin/services/${props.service.id}` },
];

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getStatusClass = (status: string) => {
  switch (status) {
    case 'active': return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300';
    case 'pending': return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300';
    case 'suspended': return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300';
    case 'terminated': return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300';
    default: return 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
  }
};

const getStatusText = (status: string) => {
  switch (status) {
    case 'active': return 'Aktif';
    case 'pending': return 'Menunggu';
    case 'suspended': return 'Ditangguhkan';
    case 'terminated': return 'Dihentikan';
    default: return status;
  }
};

const getServiceTypeText = (serviceType: string) => {
  switch (serviceType) {
    case 'hosting': return 'Hosting';
    case 'domain': return 'Domain';
    default: return serviceType;
  }
};

const isExpiringSoon = (expiresAt: string) => {
  const expiryDate = new Date(expiresAt);
  const thirtyDaysFromNow = new Date();
  thirtyDaysFromNow.setDate(thirtyDaysFromNow.getDate() + 30);
  return expiryDate <= thirtyDaysFromNow;
};

const daysUntilExpiry = () => {
  const expiryDate = new Date(props.service.expires_at);
  const today = new Date();
  const diffTime = expiryDate.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};
</script>

<template>
  <Head :title="`Layanan - ${service.domain_name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">{{ service.domain_name }}</h1>
          <p class="text-muted-foreground">Detail dan informasi layanan</p>
        </div>
        <Button variant="outline" asChild>
          <Link href="/admin/services">
            Kembali ke Layanan
          </Link>
        </Button>
      </div>

      <!-- Service Overview -->
      <div class="grid gap-6 md:grid-cols-2">
        <!-- Service Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <component :is="service.service_type === 'hosting' ? Server : Globe" class="h-5 w-5" />
              Informasi Layanan
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-medium">Status</span>
              <div class="flex items-center gap-2">
                <Badge :class="getStatusClass(service.status)">
                  {{ getStatusText(service.status) }}
                </Badge>
                <Badge v-if="isExpiringSoon(service.expires_at) && service.status === 'active'" class="bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                  Segera Berakhir
                </Badge>
              </div>
            </div>
            
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Tipe Layanan</span>
                <Badge :class="service.service_type === 'hosting' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'">
                  {{ getServiceTypeText(service.service_type) }}
                </Badge>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Domain</span>
                <span class="text-sm font-medium">{{ service.domain_name }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Perpanjang Otomatis</span>
                <Badge :variant="service.auto_renew ? 'default' : 'secondary'">
                  {{ service.auto_renew ? 'Aktif' : 'Nonaktif' }}
                </Badge>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Berakhir</span>
                <div class="text-right">
                  <div class="text-sm font-medium">{{ formatDate(service.expires_at) }}</div>
                  <div class="text-xs text-muted-foreground">
                    {{ daysUntilExpiry() > 0 ? `${daysUntilExpiry()} hari lagi` : 'Kedaluwarsa' }}
                  </div>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Dibuat</span>
                <span class="text-sm">{{ formatDate(service.created_at) }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-muted-foreground">Terakhir Diperbarui</span>
                <span class="text-sm">{{ formatDate(service.updated_at) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Customer Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Mail class="h-5 w-5" />
              Informasi Pelanggan
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <div>
                <span class="text-sm font-medium">{{ service.customer.name }}</span>
                <div class="text-sm text-muted-foreground">ID Pelanggan: #{{ service.customer.id }}</div>
              </div>
              
              <div class="flex items-center gap-3">
                <Mail class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm">{{ service.customer.email }}</span>
              </div>
              
              <div v-if="service.customer.phone" class="flex items-center gap-3">
                <Phone class="h-4 w-4 text-muted-foreground" />
                <span class="text-sm">{{ service.customer.phone }}</span>
              </div>
              
              <div v-if="service.customer.address || service.customer.city || service.customer.country" class="flex items-start gap-3">
                <MapPin class="h-4 w-4 text-muted-foreground mt-0.5" />
                <div class="text-sm">
                  <div v-if="service.customer.address">{{ service.customer.address }}</div>
                  <div>
                    {{ [service.customer.city, service.customer.country].filter(Boolean).join(', ') }}
                  </div>
                </div>
              </div>
            </div>
            
            <div class="pt-3 border-t">
              <Button size="sm" variant="outline" asChild>
                <Link :href="`/admin/customers/${service.customer.id}`">
                  Lihat Detail Pelanggan
                </Link>
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Hosting Plan Details (if hosting service) -->
      <Card v-if="service.service_type === 'hosting' && service.hosting_plan">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Server class="h-5 w-5" />
            Detail Paket Hosting
          </CardTitle>
          <CardDescription>Spesifikasi teknis dan fitur</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="text-center">
              <div class="text-2xl font-bold text-blue-600">{{ service.hosting_plan.storage_gb }}GB</div>
              <div class="text-sm text-muted-foreground">Penyimpanan</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-green-600">{{ service.hosting_plan.cpu_cores }}</div>
              <div class="text-sm text-muted-foreground">CPU Core</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-orange-600">{{ service.hosting_plan.ram_gb }}GB</div>
              <div class="text-sm text-muted-foreground">RAM</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-purple-600">{{ service.hosting_plan.bandwidth || '∞' }}</div>
              <div class="text-sm text-muted-foreground">Bandwidth</div>
            </div>
          </div>
          
          <div v-if="service.hosting_plan.features && service.hosting_plan.features.length > 0" class="mt-6">
            <h4 class="text-sm font-medium mb-3">Fitur Paket</h4>
            <div class="grid grid-cols-2 gap-2">
              <div v-for="feature in service.hosting_plan.features" :key="feature" class="text-sm text-muted-foreground">
                • {{ feature }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Service Metadata (if any) -->
      <Card v-if="service.metadata && Object.keys(service.metadata).length > 0">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Settings class="h-5 w-5" />
            Informasi Tambahan
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="(value, key) in service.metadata" :key="key" class="flex items-center justify-between">
              <span class="text-sm text-muted-foreground capitalize">{{ key.replace('_', ' ') }}</span>
              <span class="text-sm font-medium">{{ value }}</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Quick Actions -->
      <Card>
        <CardHeader>
          <CardTitle>Tindakan Cepat</CardTitle>
          <CardDescription>Tugas manajemen layanan umum</CardDescription>
        </CardHeader>
        <CardContent>
          <div class="flex flex-wrap gap-3">
            <Button v-if="service.status === 'active'" variant="outline" size="sm">
              <Clock class="h-4 w-4 mr-2" />
              Tangguhkan Layanan
            </Button>
            <Button v-if="service.status === 'suspended'" variant="outline" size="sm">
              <Settings class="h-4 w-4 mr-2" />
              Aktifkan Kembali Layanan
            </Button>
            <Button variant="outline" size="sm">
              <Calendar class="h-4 w-4 mr-2" />
              Perpanjang Masa Berlaku
            </Button>
            <Button variant="outline" size="sm">
              <Settings class="h-4 w-4 mr-2" />
              {{ service.auto_renew ? 'Nonaktifkan' : 'Aktifkan' }} Perpanjang Otomatis
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>