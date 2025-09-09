<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { CheckCircle, Globe, LayoutGrid, Server, Shield, Star, Users } from 'lucide-vue-next';

const page = usePage();
const isAdmin = page.props.auth?.user !== null; // User yang login melalui guard 'web' adalah admin

const features = [
    {
        icon: Server,
        title: 'Reliable Hosting',
        description: 'Professional web hosting with 99.9% uptime guarantee',
    },
    {
        icon: Globe,
        title: 'Domain Registration',
        description: 'Register your perfect domain with competitive pricing',
    },
    {
        icon: Shield,
        title: 'Secure & Protected',
        description: 'SSL certificates and daily backups included',
    },
    {
        icon: Users,
        title: '24/7 Support',
        description: 'Expert support team ready to help you anytime',
    },
];

const benefits = [
    'Easy-to-use control panel',
    'Free SSL certificates',
    'Daily automated backups',
    'One-click app installs',
    'Email hosting included',
    '30-day money-back guarantee',
];
</script>

<template>
    <Head title="Professional Web Hosting & Domain Services" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
        <!-- Navigation -->
        <nav class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                    <span class="text-xl font-bold">Ws.</span>
                </div>
                <div class="flex items-center space-x-4">
                    <Button variant="ghost" asChild>
                        <Link href="/hosting">Hosting</Link>
                    </Button>
                    <Button variant="ghost" asChild>
                        <Link href="/domains">Domains</Link>
                    </Button>
                    <template v-if="isAdmin">
                        <Button variant="outline" asChild>
                            <Link href="/dashboard" class="flex items-center gap-2">
                                <LayoutGrid class="h-4 w-4" />
                                Dashboard
                            </Link>
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="outline" asChild>
                            <Link href="/customer/login">Login</Link>
                        </Button>
                        <Button asChild>
                            <Link href="/customer/register">Get Started</Link>
                        </Button>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="container mx-auto px-6 py-20">
            <div class="mb-16 text-center">
                <h1 class="mb-6 text-5xl font-bold text-gray-900 md:text-6xl dark:text-white">
                    Professional Web Hosting
                    <span class="block text-blue-600 dark:text-blue-400">Made Simple</span>
                </h1>
                <p class="mx-auto mb-8 max-w-3xl text-xl text-gray-600 dark:text-gray-300">
                    Get reliable web hosting and domain registration services with expert support. Perfect for businesses, developers, and personal
                    websites.
                </p>
                <div class="flex flex-col justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                    <Button size="lg" asChild>
                        <Link href="/hosting">View Hosting Plans</Link>
                    </Button>
                    <Button size="lg" variant="outline" asChild>
                        <Link href="/domains">Search Domains</Link>
                    </Button>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="mb-16 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <Card v-for="feature in features" :key="feature.title" class="text-center">
                    <CardHeader>
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900">
                            <component :is="feature.icon" class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                        </div>
                        <CardTitle class="text-lg">{{ feature.title }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <CardDescription>{{ feature.description }}</CardDescription>
                    </CardContent>
                </Card>
            </div>

            <!-- Benefits Section -->
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <h2 class="mb-6 text-3xl font-bold text-gray-900 dark:text-white">Why Choose Our Hosting?</h2>
                    <p class="mb-8 text-gray-600 dark:text-gray-300">
                        We provide everything you need to succeed online, from reliable hosting to professional support.
                    </p>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div v-for="benefit in benefits" :key="benefit" class="flex items-center space-x-3">
                            <CheckCircle class="h-5 w-5 flex-shrink-0 text-green-500" />
                            <span class="text-gray-700 dark:text-gray-300">{{ benefit }}</span>
                        </div>
                    </div>
                </div>

                <Card class="lg:ml-8">
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Star class="h-5 w-5 text-yellow-500" />
                            <span>Customer Testimonial</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <blockquote class="mb-4 text-gray-600 italic dark:text-gray-300">
                            "Excellent service and support. Our website has been running smoothly for over a year with zero downtime. Highly
                            recommended!"
                        </blockquote>
                        <cite class="font-semibold text-gray-900 dark:text-white">- Sarah Johnson, Business Owner</cite>
                    </CardContent>
                </Card>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="bg-blue-600 py-16 dark:bg-blue-700">
            <div class="container mx-auto px-6 text-center">
                <h2 class="mb-6 text-3xl font-bold text-white">Ready to Get Started?</h2>
                <p class="mx-auto mb-8 max-w-2xl text-blue-100">Join thousands of satisfied customers who trust us with their web hosting needs.</p>
                <div class="flex flex-col justify-center space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                    <Button size="lg" variant="secondary" asChild>
                        <Link href="/customer/register">Create Account</Link>
                    </Button>
                    <Button size="lg" variant="outline" class="border-white text-white hover:bg-white hover:text-blue-600" asChild>
                        <Link href="/hosting">View Plans</Link>
                    </Button>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-900 py-8 text-gray-300">
            <div class="container mx-auto px-6">
                <div class="flex flex-col items-center justify-between md:flex-row">
                    <div class="mb-4 flex items-center space-x-2 md:mb-0">
                        <img src="/1.png" alt="WebSweetStudio" class="h-8 w-8 object-contain" />
                        <span class="text-xl font-bold text-white">Ws.</span>
                    </div>
                    <div class="flex space-x-6 text-sm">
                        <Link href="/hosting" class="hover:text-white">Hosting</Link>
                        <Link href="/domains" class="hover:text-white">Domains</Link>
                        <Link href="/customer/login" class="hover:text-white">Customer Login</Link>
                        <Link href="/login" class="hover:text-white">Admin Login</Link>
                    </div>
                </div>
                <div class="mt-6 border-t border-gray-700 pt-6 text-center text-sm">
                    <p>&copy; 2024 WebSweetStudio. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
