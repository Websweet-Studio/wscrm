<script setup lang="ts">
import AuthBase from '@/layouts/AuthLayout.vue';
import InputError from '@/components/InputError.vue';
import { register } from '@/routes';
import { request } from '@/routes/password';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
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

    <div v-if="status" class="mb-5 rounded-xl px-4 py-3 text-center text-sm" style="background-color: var(--kpub-green-soft); color: var(--kpub-green);">
        {{ status }}
    </div>

    <Form action="/login" method="post" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-5">
        <input type="hidden" name="_token" :value="csrfToken" />

        <div class="grid gap-4">
            <!-- Email / Username -->
            <div class="space-y-1.5">
                <label for="login" class="block text-sm" style="color: var(--kpub-muted); font-weight: 500;">
                    Email atau Username
                </label>
                <input
                    id="login"
                    type="text"
                    name="login"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="email@example.com"
                    class="kfield"
                />
                <InputError :message="errors.login" />
            </div>

            <!-- Password -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm" style="color: var(--kpub-muted); font-weight: 500;">
                        Password
                    </label>
                    <TextLink v-if="canResetPassword" :href="request()" class="klink !text-sm !no-underline hover:!underline">
                        Lupa password?
                    </TextLink>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Masukkan password"
                    class="kfield"
                />
                <InputError :message="errors.password" />
            </div>

            <!-- Remember -->
            <label for="remember" class="flex cursor-pointer items-center gap-2.5 text-sm select-none" style="color: var(--kpub-muted);">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    class="h-4 w-4 rounded"
                    style="accent-color: var(--kpub-green);"
                />
                Ingat saya
            </label>

            <!-- Submit -->
            <button
                type="submit"
                :disabled="processing"
                class="kbtn kbtn-primary w-full justify-center py-3 disabled:opacity-60"
            >
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                <span v-else class="flex items-center justify-center gap-2">
                    Masuk
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </span>
            </button>
        </div>

        <!-- Links -->
        <div class="space-y-1.5 text-center text-sm" style="color: var(--kpub-muted);">
            <p>
                Belum punya akun pelanggan?
                <TextLink href="/customer/register" class="klink !text-sm !no-underline hover:!underline">Daftar</TextLink>
            </p>
            <p>
                Belum punya akun admin?
                <TextLink :href="register()" class="klink !text-sm !no-underline hover:!underline">Daftar</TextLink>
            </p>
        </div>
    </Form>
</AuthBase>
</template>
