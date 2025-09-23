<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import CustomerLayout from '@/layouts/CustomerLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const page = usePage();
const customer = page.props.auth.customer;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/customer/dashboard' },
    { title: 'Settings', href: '/customer/settings' }
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
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Settings</h1>
                <p class="text-muted-foreground">Kelola informasi akun dan keamanan Anda</p>
            </div>

            <div class="grid gap-6">
                <!-- Profile Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle>Informasi Profil</CardTitle>
                        <CardDescription>
                            Perbarui informasi profil dan alamat email Anda
                        </CardDescription>
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

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="profileForm.processing">
                                    <span v-if="!profileForm.processing">Simpan Perubahan</span>
                                    <span v-else>Menyimpan...</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <!-- Password Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle>Ubah Password</CardTitle>
                        <CardDescription>
                            Perbarui password akun Anda untuk menjaga keamanan
                        </CardDescription>
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

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="passwordForm.processing">
                                    <span v-if="!passwordForm.processing">Ubah Password</span>
                                    <span v-else>Mengubah...</span>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>
    </CustomerLayout>
</template>