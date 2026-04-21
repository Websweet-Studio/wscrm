<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    address: '',
    city: '',
    country: 'Indonesia',
    postal_code: '',
});

const submit = () => {
    form.post('/customer/register');
};
</script>

<template>
    <Head title="Customer Register" />

    <AuthCardLayout>
        <div class="space-y-6">
            <div class="space-y-3 text-center">
                <h2 class="text-2xl font-medium" style="color: #141413; line-height: 1.2; font-family: Georgia, serif;">Buat Akun</h2>
                <p style="color: #5e5d59;">Bergabung hari ini dan dapatkan akses fitur eksklusif</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4" style="color: #c96442;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            ></path>
                        </svg>
                        <h3 class="text-sm font-medium" style="color: #4d4c48;">Informasi Pribadi</h3>
                    </div>

                    <div class="space-y-2">
                        <Label for="name" style="color: #4d4c48;">Nama Lengkap</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan nama lengkap Anda"
                            required
                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="email" style="color: #4d4c48;">Alamat Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Masukkan email Anda"
                            required
                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="phone" style="color: #4d4c48;">
                            Nomor Telepon <span style="color: #87867f;">(Opsional)</span>
                        </Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="contoh: +62812345678"
                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>
                </div>

                <Separator style="background-color: #f0eee6;" />

                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4" style="color: #c96442;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            ></path>
                        </svg>
                        <h3 class="text-sm font-medium" style="color: #4d4c48;">Keamanan</h3>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="password" style="color: #4d4c48;">Password</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Buat password"
                                required
                                style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <div class="space-y-2">
                            <Label for="password_confirmation" style="color: #4d4c48;">Konfirmasi Password</Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Konfirmasi password Anda"
                                required
                                style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                            />
                        </div>
                    </div>
                </div>

                <Separator style="background-color: #f0eee6;" />

                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4" style="color: #c96442;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            ></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <h3 class="text-sm font-medium" style="color: #4d4c48;">Informasi Alamat <span style="color: #87867f;">(Opsional)</span></h3>
                    </div>

                    <div class="space-y-2">
                        <Label for="address" style="color: #4d4c48;">Alamat Jalan</Label>
                        <Input
                            id="address"
                            v-model="form.address"
                            type="text"
                            placeholder="Masukkan alamat jalan Anda"
                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                        />
                        <InputError :message="form.errors.address" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="city" style="color: #4d4c48;">Kota</Label>
                            <Input
                                id="city"
                                v-model="form.city"
                                type="text"
                                placeholder="contoh: Jakarta"
                                style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                            />
                            <InputError :message="form.errors.city" />
                        </div>

                        <div class="space-y-2">
                            <Label for="postal_code" style="color: #4d4c48;">Kode Pos</Label>
                            <Input
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                placeholder="contoh: 12345"
                                style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                            />
                            <InputError :message="form.errors.postal_code" />
                        </div>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    size="lg"
                    style="background-color: #c96442; color: #faf9f5; border-radius: 12px;"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Buat Akun</span>
                    <span v-else class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Membuat akun...
                    </span>
                </Button>
            </form>

            <Separator style="background-color: #f0eee6;" />

            <div class="space-y-4 text-center">
                <div class="text-sm">
                    <span style="color: #5e5d59;">Sudah punya akun? </span>
                    <TextLink href="/customer/login" class="font-medium" style="color: #c96442;">
                        Masuk
                    </TextLink>
                </div>

                <div class="border-t pt-4" style="border-color: #f0eee6;">
                    <TextLink
                        href="/hosting"
                        class="inline-flex items-center gap-2 text-sm"
                        style="color: #5e5d59;"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke Toko
                    </TextLink>
                </div>
            </div>
        </div>
    </AuthCardLayout>
</template>
