<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Clock, Server } from 'lucide-vue-next';
</script>

<template>
    <Head title="Syarat dan Ketentuan" />

    <AuthCardLayout>
        <div class="auth-card space-y-6">
            <!-- Header Section -->
            <div class="space-y-3 text-center">
                <div class="flex items-center justify-center gap-2">
                    <FileText class="h-6 w-6 text-primary" />
                    <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Syarat dan Ketentuan</h2>
                </div>
                <p class="text-muted-foreground">Ketentuan penggunaan layanan hosting dan domain</p>
            </div>

            <!-- Terms Content -->
            <div class="space-y-6 text-sm">
                <!-- General Terms -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground flex items-center gap-2">
                        <Server class="h-4 w-4 text-blue-600" />
                        Ketentuan Umum
                    </h3>
                    <div class="space-y-2 text-muted-foreground">
                        <p>1. Dengan menggunakan layanan kami, Anda menyetujui syarat dan ketentuan yang berlaku.</p>
                        <p>2. Layanan hosting dan domain disediakan sesuai dengan paket yang dipilih.</p>
                        <p>3. Pembayaran dilakukan sesuai dengan invoice yang diterbitkan.</p>
                    </div>
                </div>

                <!-- Resource Specifications -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground">Spesifikasi Sumber Daya</h3>
                    <div class="space-y-2 text-muted-foreground bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <p class="font-medium text-blue-900">⚠️ Penting untuk diketahui:</p>
                        <p>• <strong>CPU dan RAM</strong> yang tercantum dalam paket adalah <strong>"up to"</strong> atau maksimal yang dapat digunakan.</p>
                        <p>• Penggunaan sumber daya tergantung pada kebutuhan website dan traffic yang ada.</p>
                        <p>• Sistem akan mengalokasikan sumber daya secara otomatis sesuai kebutuhan.</p>
                    </div>
                </div>

                <!-- Setup Time -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground flex items-center gap-2">
                        <Clock class="h-4 w-4 text-green-600" />
                        Waktu Setup
                    </h3>
                    <div class="space-y-2 text-muted-foreground bg-green-50 p-4 rounded-lg border border-green-200">
                        <p class="font-medium text-green-900">📅 Estimasi Waktu Setup:</p>
                        <p>• <strong>Domain dan Hosting</strong> membutuhkan waktu setup hingga <strong>2 hari kerja</strong>.</p>
                        <p>• Proses aktivasi akan dilakukan setelah pembayaran dikonfirmasi.</p>
                        <p>• Anda akan menerima email konfirmasi setelah layanan aktif.</p>
                    </div>
                </div>

                <!-- Domain Terms -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground">Ketentuan Domain</h3>
                    <div class="space-y-2 text-muted-foreground">
                        <p>1. Domain yang sudah dibeli tidak dapat dibatalkan atau dikembalikan.</p>
                        <p>2. Perpanjangan domain harus dilakukan sebelum masa expired.</p>
                        <p>3. Transfer domain ke provider lain dapat dilakukan sesuai kebijakan registry.</p>
                    </div>
                </div>

                <!-- Hosting Terms -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground">Ketentuan Hosting</h3>
                    <div class="space-y-2 text-muted-foreground">
                        <p>1. Backup data dilakukan secara otomatis, namun kami sarankan untuk melakukan backup mandiri.</p>
                        <p>2. Penggunaan yang melanggar hukum atau ToS akan mengakibatkan penangguhan layanan.</p>
                        <p>3. Support teknis tersedia melalui sistem tiket atau kontak yang tersedia.</p>
                    </div>
                </div>

                <!-- Payment Terms -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground">Ketentuan Pembayaran</h3>
                    <div class="space-y-2 text-muted-foreground">
                        <p>1. Pembayaran dilakukan sesuai dengan invoice yang diterbitkan.</p>
                        <p>2. Layanan akan diaktifkan setelah pembayaran dikonfirmasi.</p>
                        <p>3. Keterlambatan pembayaran dapat mengakibatkan penangguhan layanan.</p>
                    </div>
                </div>

                <!-- Limitation of Liability -->
                <div class="space-y-3">
                    <h3 class="text-base font-semibold text-foreground">Batasan Tanggung Jawab</h3>
                    <div class="space-y-2 text-muted-foreground text-xs bg-gray-50 p-3 rounded border">
                        <p>WebSweetStudio tidak bertanggung jawab atas kerugian yang timbul akibat:</p>
                        <p>• Kehilangan data akibat kesalahan pengguna</p>
                        <p>• Downtime yang disebabkan oleh faktor eksternal</p>
                        <p>• Pelanggaran yang dilakukan oleh pengguna layanan</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-primary/5 p-4 rounded-lg border border-primary/20">
                <p class="text-sm text-center text-primary-foreground/80">
                    <strong>Pertanyaan?</strong> Hubungi kami di 
                    <a href="mailto:websweetstudio@gmail.com" class="text-primary hover:underline">websweetstudio@gmail.com</a>
                    atau <a href="tel:+6285175227339" class="text-primary hover:underline">+62 851 75227339</a>
                </p>
            </div>

            <!-- Back to Login -->
            <div class="text-center pt-4 border-t border-muted">
                <TextLink
                    href="/customer/login"
                    class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors duration-200 hover:text-foreground"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Kembali ke Login
                </TextLink>
            </div>
        </div>
    </AuthCardLayout>
</template>