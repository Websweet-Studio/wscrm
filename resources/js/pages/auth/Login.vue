<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    csrf_token?: string;
}>();

const page = usePage();

const csrfToken = computed(() => {
    if (page.props.csrf_token) {
        return page.props.csrf_token;
    }
    return '';
});

</script>

<template>
    <AuthBase title="Masuk" description="Masukkan kredensial Anda untuk melanjutkan">
        <Head title="Masuk" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600 animate-in fade-in duration-500">
            {{ status }}
        </div>

        <Form action="/login" method="post" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-6">
            <input type="hidden" name="_token" :value="csrfToken" />

            <div class="grid gap-5">
                <!-- Email / Username -->
                <div class="space-y-2 animate-in fade-in slide-in-from-bottom-3 duration-600 delay-100 ease-out fill-mode-both">
                    <Label for="login" style="color: #4d4c48; font-size: 0.8125rem; font-weight: 500;">Email atau Username</Label>
                    <Input
                        id="login"
                        type="text"
                        name="login"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="email@example.com"
                        class="h-12 transition-all duration-200"
                        style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                    />
                    <InputError :message="errors.login" />
                </div>

                <!-- Password -->
                <div class="space-y-2 animate-in fade-in slide-in-from-bottom-3 duration-600 delay-150 ease-out fill-mode-both">
                    <div class="flex items-center justify-between">
                        <Label for="password" style="color: #4d4c48; font-size: 0.8125rem; font-weight: 500;">Password</Label>
                        <TextLink v-if="canResetPassword" :href="request()" class="text-sm" style="color: var(--primary);">
                            Lupa password?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
                        class="h-12 transition-all duration-200"
                        style="background-color: #ffffff; border: 1px solid #e8e6dc; color: #141413; border-radius: 12px;"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- Remember -->
                <div class="animate-in fade-in slide-in-from-bottom-3 duration-600 delay-200 ease-out fill-mode-both">
                    <label for="remember" class="flex cursor-pointer items-center gap-3 text-sm select-none" style="color: #5e5d59;">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="h-4 w-4 rounded"
                            style="accent-color: var(--primary);"
                        />
                        Ingat saya
                    </label>
                </div>

                <!-- Submit -->
                <div class="animate-in fade-in slide-in-from-bottom-3 duration-600 delay-250 ease-out fill-mode-both">
                    <Button
                        type="submit"
                        class="w-full h-12 text-sm font-medium transition-all duration-200 active:scale-[0.98]"
                        style="background-color: var(--primary); color: var(--primary-foreground); border-radius: 12px;"
                        :disabled="processing"
                    >
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        <span v-else class="flex items-center justify-center gap-2">
                            Masuk
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </span>
                    </Button>
                </div>
            </div>

            <!-- Links -->
            <div class="space-y-2 text-center text-sm animate-in fade-in slide-in-from-bottom-3 duration-600 delay-300 ease-out fill-mode-both">
                <p style="color: #5e5d59;">
                    Belum punya akun pelanggan?
                    <TextLink href="/customer/register" class="font-medium" style="color: var(--primary);">Daftar</TextLink>
                </p>
                <p style="color: #5e5d59;">
                    Belum punya akun admin?
                    <TextLink :href="register()" class="font-medium" style="color: var(--primary);">Daftar</TextLink>
                </p>
            </div>
        </Form>
    </AuthBase>
</template>
