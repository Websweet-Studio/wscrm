<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthCardLayout from '@/layouts/auth/AuthCardLayout.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';

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
        <div class="space-y-7">
            <!-- Header -->
            <div class="space-y-2 text-center">
                <h1
                    class="font-heading text-2xl font-medium tracking-tight animate-in fade-in slide-in-from-bottom-3 duration-600 ease-out fill-mode-both"
                    style="color: #141413; line-height: 1.25;"
                >
                    Selamat datang kembali
                </h1>
                <p
                    class="text-sm animate-in fade-in slide-in-from-bottom-3 duration-600 delay-100 ease-out fill-mode-both"
                    style="color: #5e5d59;"
                >
                    Masuk ke akun Anda untuk melanjutkan
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-5">
                <!-- Email -->
                <div
                    class="space-y-2 animate-in fade-in slide-in-from-bottom-3 duration-600 delay-150 ease-out fill-mode-both"
                >
                    <Label for="email" style="color: #4d4c48; font-size: 0.8125rem; font-weight: 500;">Alamat Email</Label>
                    <div class="relative">
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="nama@email.com"
                            required
                            autocomplete="email"
                            class="peer h-12 w-full px-4 text-sm transition-all duration-200"
                            :class="form.errors.email ? 'border-red-400' : ''"
                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <!-- Password -->
                <div
                    class="space-y-2 animate-in fade-in slide-in-from-bottom-3 duration-600 delay-200 ease-out fill-mode-both"
                >
                    <div class="flex items-center justify-between">
                        <Label for="password" style="color: #4d4c48; font-size: 0.8125rem; font-weight: 500;">Password</Label>
                    </div>
                    <div class="relative">
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Masukkan password"
                            required
                            autocomplete="current-password"
                            class="peer h-12 w-full px-4 text-sm transition-all duration-200"
                            :class="form.errors.password ? 'border-red-400' : ''"
                            style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                        />
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <!-- Remember + Forgot -->
                <div
                    class="flex items-center justify-between animate-in fade-in slide-in-from-bottom-3 duration-600 delay-250 ease-out fill-mode-both"
                >
                    <div class="flex items-center gap-3">
                        <Checkbox id="remember" v-model:checked="form.remember" />
                        <Label for="remember" class="cursor-pointer text-sm select-none" style="color: #5e5d59;">
                            Ingat saya
                        </Label>
                    </div>
                </div>

                <!-- Submit -->
                <div
                    class="animate-in fade-in slide-in-from-bottom-3 duration-600 delay-300 ease-out fill-mode-both"
                >
                    <Button
                        type="submit"
                        class="relative w-full h-12 overflow-hidden text-sm font-medium transition-all duration-200 active:scale-[0.98]"
                        style="background-color: var(--primary); color: var(--primary-foreground); border-radius: 12px;"
                        :disabled="form.processing"
                    >
                        <span v-if="!form.processing" class="flex items-center justify-center gap-2">
                            Masuk
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </span>
                        <span v-else class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            Memproses...
                        </span>
                    </Button>
                </div>
            </form>

            <!-- Divider -->
            <div
                class="animate-in fade-in slide-in-from-bottom-3 duration-600 delay-350 ease-out fill-mode-both"
            >
                <div class="flex items-center gap-4" style="color: #e8e6dc;">
                    <div class="h-px flex-1" style="background-color: #f0eee6;"></div>
                    <span class="text-xs tracking-wide" style="color: #87867f;">atau</span>
                    <div class="h-px flex-1" style="background-color: #f0eee6;"></div>
                </div>
            </div>

            <!-- Links -->
            <div
                class="space-y-3 text-center animate-in fade-in slide-in-from-bottom-3 duration-600 delay-400 ease-out fill-mode-both"
            >
                <p class="text-sm" style="color: #5e5d59;">
                    Belum punya akun?
                    <TextLink href="/customer/register" class="font-medium" style="color: var(--primary);">
                        Daftar sekarang
                    </TextLink>
                </p>

                <div class="pt-2">
                    <TextLink
                        href="/hosting"
                        class="inline-flex items-center gap-1.5 text-xs transition-colors duration-200 hover:opacity-80"
                        style="color: #87867f;"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Kembali ke beranda
                    </TextLink>
                </div>
            </div>
        </div>
    </AuthCardLayout>
</template>
