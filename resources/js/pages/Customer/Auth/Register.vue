<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
        <div class="auth-card space-y-6">
            <!-- Header Section -->
            <div class="space-y-3 text-center">
                <h2 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Create Account</h2>
                <p class="text-muted-foreground">Join us today and get access to exclusive features</p>
            </div>

            <!-- Registration Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Personal Information Section -->
                <div class="space-y-4">
                    <div class="form-section-divider flex items-center gap-2 border-b border-muted pb-2">
                        <svg class="section-icon h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            ></path>
                        </svg>
                        <h3 class="text-sm font-semibold tracking-wide text-foreground uppercase">Personal Information</h3>
                    </div>

                    <!-- Name Field -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium text-foreground">Full Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Enter your full name"
                            required
                            class="auth-input enhanced-focus animate-delay-100 py-3 pr-4 pl-4 text-base"
                            :class="form.errors.name ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                        />
                        <InputError class="text-xs" :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium text-foreground">Email Address</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Enter your email"
                            required
                            class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                            :class="form.errors.email ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                        />
                        <InputError class="text-xs" :message="form.errors.email" />
                    </div>

                    <!-- Phone Field -->
                    <div class="space-y-2">
                        <Label for="phone" class="text-sm font-medium text-foreground"
                            >Phone Number <span class="text-xs text-muted-foreground">(Optional)</span></Label
                        >
                        <Input
                            id="phone"
                            v-model="form.phone"
                            type="tel"
                            placeholder="e.g., +62812345678"
                            class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                            :class="form.errors.phone ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                        />
                        <InputError class="text-xs" :message="form.errors.phone" />
                    </div>
                </div>

                <!-- Security Section -->
                <div class="space-y-4">
                    <div class="form-section-divider flex items-center gap-2 border-b border-muted pb-2">
                        <svg class="section-icon h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                            ></path>
                        </svg>
                        <h3 class="text-sm font-semibold tracking-wide text-foreground uppercase">Security</h3>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- Password Field -->
                        <div class="space-y-2">
                            <Label for="password" class="text-sm font-medium text-foreground">Password</Label>
                            <Input
                                id="password"
                                v-model="form.password"
                                type="password"
                                placeholder="Create a password"
                                required
                                class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                                :class="form.errors.password ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                            />
                            <InputError class="text-xs" :message="form.errors.password" />
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-sm font-medium text-foreground">Confirm Password</Label>
                            <Input
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="Confirm your password"
                                required
                                class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                            />
                        </div>
                    </div>
                </div>

                <!-- Address Section -->
                <div class="space-y-4">
                    <div class="form-section-divider flex items-center gap-2 border-b border-muted pb-2">
                        <svg class="section-icon h-4 w-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                            ></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <h3 class="text-sm font-semibold tracking-wide text-foreground uppercase">Address Information</h3>
                        <span class="text-xs text-muted-foreground">(Optional)</span>
                    </div>

                    <!-- Address Field -->
                    <div class="space-y-2">
                        <Label for="address" class="text-sm font-medium text-foreground">Street Address</Label>
                        <Input
                            id="address"
                            v-model="form.address"
                            type="text"
                            placeholder="Enter your street address"
                            class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                            :class="form.errors.address ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                        />
                        <InputError class="text-xs" :message="form.errors.address" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <!-- City Field -->
                        <div class="space-y-2">
                            <Label for="city" class="text-sm font-medium text-foreground">City</Label>
                            <Input
                                id="city"
                                v-model="form.city"
                                type="text"
                                placeholder="e.g., Jakarta"
                                class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                                :class="form.errors.city ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                            />
                            <InputError class="text-xs" :message="form.errors.city" />
                        </div>

                        <!-- Postal Code Field -->
                        <div class="space-y-2">
                            <Label for="postal_code" class="text-sm font-medium text-foreground">Postal Code</Label>
                            <Input
                                id="postal_code"
                                v-model="form.postal_code"
                                type="text"
                                placeholder="e.g., 12345"
                                class="auth-input enhanced-focus py-3 pr-4 pl-4 text-base"
                                :class="form.errors.postal_code ? 'border-destructive focus:border-destructive focus:ring-destructive/20' : ''"
                            />
                            <InputError class="text-xs" :message="form.errors.postal_code" />
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <Button
                    type="submit"
                    class="auth-button animate-delay-400 w-full bg-primary py-3 text-base font-semibold hover:bg-primary/90 focus:ring-2 focus:ring-primary/20"
                    :disabled="form.processing"
                >
                    <span v-if="!form.processing">Create Account</span>
                    <span v-else class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path
                                class="opacity-75"
                                fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                            ></path>
                        </svg>
                        Creating account...
                    </span>
                </Button>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <span class="w-full border-t border-muted" />
                </div>
                <div class="relative flex justify-center text-xs uppercase">
                    <span class="bg-card px-4 font-medium text-muted-foreground">Already a member?</span>
                </div>
            </div>

            <!-- Footer Links -->
            <div class="space-y-4 text-center">
                <div class="text-sm">
                    <span class="text-muted-foreground">Already have an account? </span>
                    <TextLink href="/customer/login" class="font-semibold text-primary transition-colors duration-200 hover:text-primary/80">
                        Sign in
                    </TextLink>
                </div>

                <div class="border-t border-muted pt-4">
                    <TextLink
                        href="/hosting"
                        class="inline-flex items-center gap-2 text-sm text-muted-foreground transition-colors duration-200 hover:text-foreground"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Shop
                    </TextLink>
                </div>
            </div>
        </div>
    </AuthCardLayout>
</template>
