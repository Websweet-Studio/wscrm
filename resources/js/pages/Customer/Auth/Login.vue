<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/customer/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Customer Login" />

    <AuthCardLayout>
        <div class="space-y-6">
            <div class="space-y-3 text-center">
                <h2 class="text-2xl font-medium" style="color: #141413; line-height: 1.2; font-family: Georgia, serif;">Masuk</h2>
                <p style="color: #5e5d59;">Masukkan kredensial Anda untuk mengakses akun</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
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
                    <Label for="password" style="color: #4d4c48;">Password</Label>
                    <Input
                        id="password"
                        v-model="form.password"
                        type="password"
                        placeholder="Masukkan password Anda"
                        required
                        style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex items-center">
                    <div class="flex items-center space-x-3">
                        <Checkbox id="remember" v-model:checked="form.remember" />
                        <Label for="remember" class="cursor-pointer text-sm" style="color: #5e5d59;"> Ingat saya selama 30 hari </Label>
                    </div>
                </div>

                <Button
                    type="submit"
                    class="w-full"
                    size="lg"
                    style="background-color: #c96442; color: #faf9f5; border-radius: 12px;"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Masuk</span>
                    <span v-else class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Masuk...
                    </span>
                </Button>
            </form>

            <Separator style="background-color: #f0eee6;" />

            <div class="space-y-4 text-center">
                <div class="text-sm">
                    <span style="color: #5e5d59;">Tidak punya akun? </span>
                    <TextLink href="/customer/register" class="font-medium" style="color: #c96442;">
                        Buat akun
                    </TextLink>
                </div>

                <div class="text-sm">
                    <TextLink href="/customer/terms" style="color: #87867f;">
                        Syarat dan Ketentuan
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
