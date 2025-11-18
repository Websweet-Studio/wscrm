<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { Form, Head } from '@inertiajs/vue3';
import { Check, LoaderCircle, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const username = ref('');
const usernameStatus = ref<'idle' | 'checking' | 'available' | 'taken' | 'invalid'>('idle');
const usernameMessage = ref('');
let debounceTimer: NodeJS.Timeout;

const checkUsernameAvailability = async (value: string) => {
    if (!value || value.length < 3) {
        usernameStatus.value = 'idle';
        usernameMessage.value = '';
        return;
    }

    usernameStatus.value = 'checking';

    try {
        const response = await fetch(`/api/username/check?username=${encodeURIComponent(value)}`);
        const data = await response.json();

        usernameStatus.value = data.status;
        usernameMessage.value = data.message;
    } catch (error) {
        usernameStatus.value = 'invalid';
        usernameMessage.value = 'Error checking username';
    }
};

watch(username, (newValue) => {
    clearTimeout(debounceTimer);
    if (newValue && newValue.length >= 3) {
        debounceTimer = setTimeout(() => {
            checkUsernameAvailability(newValue);
        }, 500);
    } else {
        usernameStatus.value = 'idle';
        usernameMessage.value = '';
    }
});
</script>

<template>
    <AuthBase title="Buat akun baru" description="Masukkan detail Anda untuk membuat akun">
        <Head title="Daftar" />

        <Form
            action="/register"
            method="post"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Nama Lengkap</Label>
                    <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" name="name" placeholder="Nama lengkap" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="username">Username</Label>
                    <div class="relative">
                        <Input
                            id="username"
                            type="text"
                            required
                            :tabindex="2"
                            autocomplete="username"
                            name="username"
                            placeholder="Username (minimal 3 karakter)"
                            v-model="username"
                            @input="(e: Event) => (username = (e.target as HTMLInputElement).value)"
                        />
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <LoaderCircle v-if="usernameStatus === 'checking'" class="h-4 w-4 animate-spin text-gray-400" />
                            <Check v-else-if="usernameStatus === 'available'" class="h-4 w-4 text-green-500" />
                            <X v-else-if="usernameStatus === 'taken' || usernameStatus === 'invalid'" class="h-4 w-4 text-red-500" />
                        </div>
                    </div>
                    <div
                        v-if="usernameMessage"
                        class="text-sm"
                        :class="{
                            'text-green-600': usernameStatus === 'available',
                            'text-red-600': usernameStatus === 'taken' || usernameStatus === 'invalid',
                        }"
                    >
                        {{ usernameMessage }}
                    </div>
                    <InputError :message="errors.username" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" required :tabindex="3" autocomplete="email" name="email" placeholder="email@example.com" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" required :tabindex="4" autocomplete="new-password" name="password" placeholder="Password" />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Konfirmasi Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="5"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Konfirmasi password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="6"
                    :disabled="processing || usernameStatus === 'taken' || usernameStatus === 'invalid'"
                >
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Buat Akun
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Sudah punya akun?
                <TextLink :href="login()" class="underline underline-offset-4" :tabindex="7">Masuk</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
