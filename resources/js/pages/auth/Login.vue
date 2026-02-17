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

defineProps<{
    status?: string;
    canResetPassword: boolean;
    csrf_token?: string;
}>();

const page = usePage();

const csrfToken = computed(() => {
    // Get CSRF token from Inertia props
    if (page.props.csrf_token) {
        return page.props.csrf_token;
    }
    return '';
});
</script>

<template>
    <AuthBase title="Masuk ke akun Anda" description="Masukkan email/username dan password untuk masuk">
        <Head title="Masuk" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <Form action="/login" method="post" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-6">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" :value="csrfToken" />

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="login">Email atau Username</Label>
                    <Input
                        id="login"
                        type="text"
                        name="login"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="username"
                        placeholder="email@example.com atau username"
                    />
                    <InputError :message="errors.login" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink v-if="canResetPassword" :href="request()" class="text-sm" :tabindex="5"> Lupa password? </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <label for="remember" class="flex items-center space-x-3 text-sm">
                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            :tabindex="3"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <span>Ingat saya</span>
                    </label>
                </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="processing">
                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                    Masuk
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                <div>
                    Belum punya akun?
                    <TextLink :href="register()" :tabindex="5">Daftar</TextLink>
                </div>
                <div v-if="canResetPassword" class="mt-2">
                    Lupa password?
                    <TextLink :href="request()" :tabindex="6">Reset di sini</TextLink>
                </div>
            </div>
        </Form>
    </AuthBase>
</template>
