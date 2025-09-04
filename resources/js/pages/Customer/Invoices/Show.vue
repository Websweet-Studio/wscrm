<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Separator } from '@/components/ui/separator'
import { FileText, CreditCard, Building2, Calendar, User, Package } from 'lucide-vue-next'
import { formatPrice, formatDate } from '@/utils/formatters'

const props = defineProps({
  invoice: Object,
  banks: Array,
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

const isOverdue = () => {
  if (props.invoice.status === 'paid') return false
  return new Date(props.invoice.due_date) < new Date()
}

const canPay = () => {
  return props.invoice.status !== 'paid' && props.invoice.status !== 'cancelled'
}
</script>

<template>
  <Head :title="`Invoice ${invoice.invoice_number}`" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            Invoice {{ invoice.invoice_number }}
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Detail invoice dan informasi pembayaran
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Link
            :href="'/customer/invoices'"
            class="text-sm text-muted-foreground hover:text-foreground"
          >
            ← Kembali ke Daftar Invoice
          </Link>
          <Button
            v-if="canPay()"
            :as="Link"
            :href="`/customer/invoices/${invoice.id}/payment`"
          >
            <CreditCard class="h-4 w-4 mr-2" />
            Bayar Sekarang
          </Button>
        </div>
      </div>
    </template>

    <div class="py-12">
      <div class="mx-auto max-w-4xl sm:px-6 lg:px-8 space-y-6">
        <!-- Invoice Status Alert -->
        <div v-if="isOverdue()" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <Calendar class="h-5 w-5 text-red-400" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                Invoice Terlambat
              </h3>
              <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                Invoice ini sudah melewati tanggal jatuh tempo. Silakan lakukan pembayaran segera.
              </p>
            </div>
          </div>
        </div>

        <div v-else-if="invoice.status === 'paid'" class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CreditCard class="h-5 w-5 text-green-400" />
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                Pembayaran Berhasil
              </h3>
              <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                Invoice ini sudah dibayar pada {{ formatDate(invoice.paid_at) }}.
              </p>
            </div>
          </div>
        </div>

        <!-- Invoice Details -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <FileText class="h-5 w-5" />
              Detail Invoice
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Invoice Number</label>
                  <p class="text-lg font-semibold">{{ invoice.invoice_number }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Status</label>
                  <div class="mt-1">
                    <Badge :class="getStatusClass(invoice.status)">
                      {{ getStatusText(invoice.status) }}
                    </Badge>
                  </div>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Total Amount</label>
                  <p class="text-2xl font-bold text-primary">{{ formatPrice(invoice.amount) }}</p>
                </div>
              </div>
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Due Date</label>
                  <p class="text-lg">{{ formatDate(invoice.due_date) }}</p>
                </div>
                <div v-if="invoice.paid_at">
                  <label class="text-sm font-medium text-muted-foreground">Paid Date</label>
                  <p class="text-lg">{{ formatDate(invoice.paid_at) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Created Date</label>
                  <p class="text-lg">{{ formatDate(invoice.created_at) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Customer Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="h-5 w-5" />
              Informasi Customer
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div>
                <label class="text-sm font-medium text-muted-foreground">Nama</label>
                <p class="font-medium">{{ invoice.customer.name }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-muted-foreground">Email</label>
                <p>{{ invoice.customer.email }}</p>
              </div>
              <div v-if="invoice.customer.phone">
                <label class="text-sm font-medium text-muted-foreground">Telepon</label>
                <p>{{ invoice.customer.phone }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Order Information -->
        <Card v-if="invoice.order">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Package class="h-5 w-5" />
              Informasi Order
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2">
              <div>
                <label class="text-sm font-medium text-muted-foreground">Order Number</label>
                <p class="font-medium">{{ invoice.order.order_number }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-muted-foreground">Order Type</label>
                <p>{{ invoice.order.order_type }}</p>
              </div>
              <div>
                <label class="text-sm font-medium text-muted-foreground">Billing Cycle</label>
                <p>{{ invoice.order.billing_cycle }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Payment Information -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Building2 class="h-5 w-5" />
              Informasi Pembayaran
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="invoice.bank" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Bank</label>
                  <p class="font-medium">{{ invoice.bank.bank_name }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Bank Code</label>
                  <p>{{ invoice.bank.bank_code }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Account Number</label>
                  <p class="font-mono">{{ invoice.bank.account_number }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-muted-foreground">Account Name</label>
                  <p>{{ invoice.bank.account_name }}</p>
                </div>
              </div>
              <div v-if="invoice.bank.branch">
                <label class="text-sm font-medium text-muted-foreground">Branch</label>
                <p>{{ invoice.bank.branch }}</p>
              </div>
              <div v-if="invoice.bank.swift_code">
                <label class="text-sm font-medium text-muted-foreground">SWIFT Code</label>
                <p class="font-mono">{{ invoice.bank.swift_code }}</p>
              </div>
              <div v-if="invoice.payment_method">
                <label class="text-sm font-medium text-muted-foreground">Payment Method</label>
                <p>{{ invoice.payment_method }}</p>
              </div>
              <div v-if="invoice.bank.admin_fee > 0">
                <label class="text-sm font-medium text-muted-foreground">Admin Fee</label>
                <p>{{ formatPrice(invoice.bank.admin_fee) }}</p>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              <Building2 class="mx-auto h-12 w-12 text-muted-foreground/40" />
              <h3 class="mt-2 text-sm font-semibold">
                Metode Pembayaran Belum Dipilih
              </h3>
              <p class="mt-1 text-sm">
                Silakan pilih metode pembayaran untuk melanjutkan.
              </p>
              <Button
                v-if="canPay()"
                :as="Link"
                :href="`/customer/invoices/${invoice.id}/payment`"
                class="mt-4"
              >
                <CreditCard class="h-4 w-4 mr-2" />
                Pilih Metode Pembayaran
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Notes -->
        <Card v-if="invoice.notes">
          <CardHeader>
            <CardTitle>Catatan</CardTitle>
          </CardHeader>
          <CardContent>
            <p class="text-sm text-muted-foreground">{{ invoice.notes }}</p>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>