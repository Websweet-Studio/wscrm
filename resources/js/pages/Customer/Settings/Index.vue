<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ArrowRight, KeyRound, Save, ShieldCheck, UserRound } from 'lucide-vue-next';

const page = usePage();
const customer = page.props.auth.customer;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Settings', href: '/customer/settings' },
];

// Profile form
const profileForm = useForm({
    name: customer?.name || '',
    email: customer?.email || '',
    phone: customer?.phone || '',
    address: customer?.address || '',
    city: customer?.city || '',
    country: customer?.country || '',
    postal_code: customer?.postal_code || '',
});

const submitProfile = () => {
    profileForm.patch('/customer/settings/profile', {
        preserveScroll: true,
    });
};

// Password form
const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submitPassword = () => {
    passwordForm.patch('/customer/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};
</script>

<template>
    <Head title="Settings" />

    <CustomerLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-4 p-4 sm:space-y-6 sm:p-6">
            <Card class="relative overflow-hidden border-border/60 bg-card/70 shadow-sm backdrop-blur">
                <div class="pointer-events-none absolute inset-0 opacity-60 dark:opacity-80">
                    <div class="absolute -inset-24 bg-[radial-gradient(closest-side,rgba(16,185,129,0.16),transparent_65%)]"></div>
                    <div class="absolute -right-24 -top-32 h-96 w-96 bg-[radial-gradient(closest-side,rgba(34,211,238,0.14),transparent_60%)]"></div>
                    <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0,rgba(16,185,129,0.05)_50%,transparent_100%)]"></div>
                </div>
                <CardContent class="relative p-4 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0">
                            <div class="mb-2 inline-flex items-center gap-2 rounded-full border border-border/60 bg-background/60 px-2.5 py-1 text-xs text-muted-foreground">
                                <ShieldCheck class="h-3.5 w-3.5 text-emerald-600 dark:text-green-400" />
                                <span>Pengaturan Akun</span>
                            </div>
                            <h1 class="font-serif text-2xl font-medium leading-tight tracking-tight sm:text-3xl">Settings</h1>
                            <p class="mt-1 text-sm text-muted-foreground sm:text-base">Kelola informasi profil dan keamanan akun Anda</p>
                        </div>
                        <div class="flex w-full flex-col gap-2 sm:w-auto sm:min-w-[260px]">
                            <Button asChild size="sm" class="w-full justify-between">
                                <a href="/customer/dashboard">
                                    <span class="inline-flex items-center gap-2">
                                        <UserRound class="h-4 w-4" />
                                        Kembali ke Dashboard
                                    </span>
                                    <ArrowRight class="h-4 w-4 opacity-80" />
                                </a>
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="grid gap-6 lg:grid-cols-5">
                <!-- Profile Settings -->
                <Card class="rounded-lg border-border/60 shadow-sm lg:col-span-3">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 font-serif font-medium tracking-tight">
                            <UserRound class="h-5 w-5 text-emerald-600 dark:text-green-400" />
                            Informasi Profil
                        </CardTitle>
                        <CardDescription class="leading-relaxed"> Perbarui informasi profil dan alamat email Anda </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitProfile" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name">Nama Lengkap</Label>
                                    <Input
                                        id="name"
                                        v-model="profileForm.name"
                                        type="text"
                                        required
                                        :class="profileForm.errors.name ? 'border-destructive' : ''"
                                    />
                                    <InputError :message="profileForm.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="email">Email</Label>
                                    <Input
                                        id="email"
                                        v-model="profileForm.email"
                                        type="email"
                                        required
                                        :class="profileForm.errors.email ? 'border-destructive' : ''"
                                    />
                                    <InputError :message="profileForm.errors.email" />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="phone">Nomor Telepon</Label>
                                    <Input
                                        id="phone"
                                        v-model="profileForm.phone"
                                        type="tel"
                                        :class="profileForm.errors.phone ? 'border-destructive' : ''"
                                    />
                                    <InputError :message="profileForm.errors.phone" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="city">Kota</Label>
                                    <Input
                                        id="city"
                                        v-model="profileForm.city"
                                        type="text"
                                        :class="profileForm.errors.city ? 'border-destructive' : ''"
                                    />
                                    <InputError :message="profileForm.errors.city" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Alamat</Label>
                                <Input
                                    id="address"
                                    v-model="profileForm.address"
                                    type="text"
                                    :class="profileForm.errors.address ? 'border-destructive' : ''"
                                />
                                <InputError :message="profileForm.errors.address" />
                            </div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="country">Negara</Label>
                                    <Input
                                        id="country"
                                        v-model="profileForm.country"
                                        type="text"
                                        :class="profileForm.errors.country ? 'border-destructive' : ''"
                                    />
                                    <InputError :message="profileForm.errors.country" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="postal_code">Kode Pos</Label>
                                    <Input
                                        id="postal_code"
                                        v-model="profileForm.postal_code"
                                        type="text"
                                        :class="profileForm.errors.postal_code ? 'border-destructive' : ''"
                                    />
                                    <InputError :message="profileForm.errors.postal_code" />
                                </div>
                            </div>

                            <div class="flex">
                                <Button type="submit" :disabled="profileForm.processing" class="w-full justify-between sm:ml-auto sm:w-auto">
                                    <span v-if="!profileForm.processing">Simpan Perubahan</span>
                                    <span v-else>Menyimpan...</span>
                                    <Save v-if="!profileForm.processing" class="ml-2 h-4 w-4" />
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Password Settings -->
                <Card class="rounded-lg border-border/60 shadow-sm lg:col-span-2">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 font-serif font-medium tracking-tight">
                            <KeyRound class="h-5 w-5 text-emerald-600 dark:text-green-400" />
                            Ubah Password
                        </CardTitle>
                        <CardDescription class="leading-relaxed"> Perbarui password akun Anda untuk menjaga keamanan </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submitPassword" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="current_password">Password Saat Ini</Label>
                                <Input
                                    id="current_password"
                                    v-model="passwordForm.current_password"
                                    type="password"
                                    required
                                    :class="passwordForm.errors.current_password ? 'border-destructive' : ''"
                                />
                                <InputError :message="passwordForm.errors.current_password" />
                            </div>

                            <div class="space-y-2">
                                <Label for="password">Password Baru</Label>
                                <Input
                                    id="password"
                                    v-model="passwordForm.password"
                                    type="password"
                                    required
                                    :class="passwordForm.errors.password ? 'border-destructive' : ''"
                                />
                                <InputError :message="passwordForm.errors.password" />
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation">Konfirmasi Password Baru</Label>
                                <Input
                                    id="password_confirmation"
                                    v-model="passwordForm.password_confirmation"
                                    type="password"
                                    required
                                    :class="passwordForm.errors.password_confirmation ? 'border-destructive' : ''"
                                />
                                <InputError :message="passwordForm.errors.password_confirmation" />
                            </div>

                            <div class="flex">
                                <Button type="submit" :disabled="passwordForm.processing" class="w-full justify-between sm:ml-auto sm:w-auto">
                                    <span v-if="!passwordForm.processing">Ubah Password</span>
                                    <span v-else>Mengubah...</span>
                                    <KeyRound v-if="!passwordForm.processing" class="ml-2 h-4 w-4" />
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </CustomerLayout>
</template>
