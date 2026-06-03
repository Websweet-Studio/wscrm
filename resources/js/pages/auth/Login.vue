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
import { computed, ref } from 'vue';

const props = defineProps<{
    status?: string;
    canResetPassword: boolean;
    csrf_token?: string;
    loginType?: string;
}>();

const page = usePage();

const csrfToken = computed(() => {
    if (page.props.csrf_token) {
        return page.props.csrf_token;
    }
    return '';
});

const activeTab = ref(props.loginType === 'customer' ? 'customer' : 'admin');
</script>

<template>
    <AuthBase title="Masuk ke akun Anda" description="Masukkan kredensial Anda untuk melanjutkan">
        <Head title="Masuk" />

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Tab Switcher -->
        <div class="mb-6 flex rounded-lg border p-1" style="background: #f5f5f5;">
            <button
                type="button"
                :class="['flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all', activeTab === 'admin' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                @click="activeTab = 'admin'"
            >
                Admin
            </button>
            <button
                type="button"
                :class="['flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all', activeTab === 'customer' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700']"
                @click="activeTab = 'customer'"
            >
                Pelanggan
            </button>
        </div>

        <!-- Admin Login Form -->
        <div v-if="activeTab === 'admin'">
            <Form action="/login" method="post" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-6">
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
                            autocomplete="username"
                            placeholder="email@example.com atau username"
                        />
                        <InputError :message="errors.login" />
                    </div>

                    <div class="grid gap-2">
                        <div class="flex items-center justify-between">
                            <Label for="password">Password</Label>
                            <TextLink v-if="canResetPassword" :href="request()" class="text-sm"> Lupa password? </TextLink>
                        </div>
                        <Input
                            id="password"
                            type="password"
                            name="password"
                            required
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
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <Button type="submit" class="mt-4 w-full" :disabled="processing">
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        Masuk sebagai Admin
                    </Button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    <div>
                        Belum punya akun admin?
                        <TextLink :href="register()">Daftar</TextLink>
                    </div>
                    <div v-if="canResetPassword" class="mt-2">
                        Lupa password?
                        <TextLink :href="request()">Reset di sini</TextLink>
                    </div>
                </div>
            </Form>
        </div>

        <!-- Customer Login Form -->
        <div v-else>
            <Form action="/customer/login" method="post" :reset-on-success="['password']" v-slot="{ errors, processing }" class="flex flex-col gap-6">
                <input type="hidden" name="_token" :value="csrfToken" />

                <div class="grid gap-6">
                    <div class="grid gap-2">
                        <Label for="customer_email">Alamat Email</Label>
                        <Input
                            id="customer_email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            placeholder="email@example.com"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="customer_password">Password</Label>
                        <Input
                            id="customer_password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Password"
                        />
                        <InputError :message="errors.password" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="customer_remember" class="flex items-center space-x-3 text-sm">
                            <input
                                type="checkbox"
                                id="customer_remember"
                                name="remember"
                                class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                            />
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <Button type="submit" class="mt-4 w-full" :disabled="processing">
                        <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                        Masuk sebagai Pelanggan
                    </Button>
                </div>

                <div class="text-center text-sm text-muted-foreground">
                    Belum punya akun?
                    <TextLink href="/customer/register">Daftar di sini</TextLink>
                </div>
            </Form>
        </div>
    </AuthBase>
</template>
