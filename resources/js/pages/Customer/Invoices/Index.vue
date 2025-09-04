<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { FileText, CreditCard, Eye } from 'lucide-vue-next'
import { formatPrice, formatDate } from '@/lib/utils'

defineProps({
  invoices: Object,
})

const getStatusClass = (status) => {
  const classes = {
    'draft': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
    'sent': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    'paid': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'overdue': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    'cancelled': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
  }
  return classes[status] || classes.draft
}

const getStatusText = (status) => {
  const texts = {
    'draft': 'Draft',
    'sent': 'Terkirim',
    'paid': 'Dibayar',
    'overdue': 'Terlambat',
    'cancelled': 'Dibatalkan',
  }
  return texts[status] || status
}
</script>

<template>
  <Head title="Invoice Saya" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Invoice Saya
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Kelola dan bayar invoice Anda
          </p>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <FileText class="h-5 w-5" />
              Daftar Invoice
            </CardTitle>
            <CardDescription>
              Semua invoice yang terkait dengan akun Anda
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="invoices.data.length > 0">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Invoice</TableHead>
                    <TableHead>Order</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead>Due Date</TableHead>
                    <TableHead>Bank</TableHead>
                    <TableHead class="text-right">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="invoice in invoices.data" :key="invoice.id">
                    <TableCell>
                      <div>
                        <div class="font-medium">{{ invoice.invoice_number }}</div>
                        <div class="text-sm text-muted-foreground">
                          {{ formatDate(invoice.created_at) }}
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div v-if="invoice.order">
                        <div class="font-medium">{{ invoice.order.order_number }}</div>
                        <div class="text-sm text-muted-foreground">{{ invoice.order.order_type }}</div>
                      </div>
                      <div v-else class="text-sm text-muted-foreground">
                        Service Invoice
                      </div>
                    </TableCell>
                    <TableCell>
                      <span class="font-medium">{{ formatPrice(invoice.amount) }}</span>
                    </TableCell>
                    <TableCell>
                      <Badge :class="getStatusClass(invoice.status)">
                        {{ getStatusText(invoice.status) }}
                      </Badge>
                    </TableCell>
                    <TableCell>
                      <div class="text-sm">
                        {{ formatDate(invoice.due_date) }}
                        <div v-if="invoice.paid_at" class="text-xs text-muted-foreground">
                          Paid: {{ formatDate(invoice.paid_at) }}
                        </div>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div v-if="invoice.bank" class="text-sm">
                        <div class="font-medium">{{ invoice.bank.bank_name }}</div>
                        <div class="text-muted-foreground">{{ invoice.bank.bank_code }}</div>
                      </div>
                      <div v-else class="text-sm text-muted-foreground">
                        Belum dipilih
                      </div>
                    </TableCell>
                    <TableCell class="text-right">
                      <div class="flex items-center justify-end gap-2">
                        <Button
                          :as="Link"
                          :href="`/customer/invoices/${invoice.id}`"
                          variant="outline"
                          size="sm"
                        >
                          <Eye class="h-4 w-4" />
                        </Button>
                        <Button
                          v-if="invoice.status !== 'paid' && invoice.status !== 'cancelled'"
                          :as="Link"
                          :href="`/customer/invoices/${invoice.id}/payment`"
                          size="sm"
                        >
                          <CreditCard class="h-4 w-4 mr-1" />
                          Bayar
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>

              <!-- Pagination -->
              <div v-if="invoices.links" class="mt-6 flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                  Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} results
                </div>
                <div class="flex items-center gap-2">
                  <Link
                    v-for="link in invoices.links"
                    :key="link.label"
                    :href="link.url"
                    :class="[
                      'px-3 py-2 text-sm rounded-md',
                      link.active
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-background border hover:bg-muted',
                      !link.url ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
                    ]"
                    v-html="link.label"
                  />
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-12">
              <FileText class="mx-auto h-12 w-12 text-muted-foreground/40" />
              <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                Belum ada invoice
              </h3>
              <p class="mt-1 text-sm text-muted-foreground">
                Invoice Anda akan muncul di sini setelah melakukan pemesanan.
              </p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>