<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Server, Globe, Shield, Users, Star, CheckCircle } from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';

const features = [
  {
    icon: Server,
    title: 'Reliable Hosting',
    description: 'Professional web hosting with 99.9% uptime guarantee'
  },
  {
    icon: Globe,
    title: 'Domain Registration',
    description: 'Register your perfect domain with competitive pricing'
  },
  {
    icon: Shield,
    title: 'Secure & Protected',
    description: 'SSL certificates and daily backups included'
  },
  {
    icon: Users,
    title: '24/7 Support',
    description: 'Expert support team ready to help you anytime'
  }
];

const benefits = [
  'Easy-to-use control panel',
  'Free SSL certificates',
  'Daily automated backups',
  'One-click app installs',
  'Email hosting included',
  '30-day money-back guarantee'
];
</script>

<template>
  <Head title="Professional Web Hosting & Domain Services" />

  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800">
    <!-- Navigation -->
    <nav class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <img src="/1.png" alt="WebSweetStudio" class="w-8 h-8 object-contain" />
          <span class="text-xl font-bold">Ws.</span>
        </div>
        <div class="flex items-center space-x-4">
          <Button variant="ghost" asChild>
            <Link href="/hosting">Hosting</Link>
          </Button>
          <Button variant="ghost" asChild>
            <Link href="/domains">Domains</Link>
          </Button>
          <Button variant="outline" asChild>
            <Link href="/customer/login">Login</Link>
          </Button>
          <Button asChild>
            <Link href="/customer/register">Get Started</Link>
          </Button>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <section class="container mx-auto px-6 py-20">
      <div class="text-center mb-16">
        <h1 class="text-5xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
          Professional Web Hosting
          <span class="text-blue-600 dark:text-blue-400 block">Made Simple</span>
        </h1>
        <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-3xl mx-auto">
          Get reliable web hosting and domain registration services with expert support. 
          Perfect for businesses, developers, and personal websites.
        </p>
        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
          <Button size="lg" asChild>
            <Link href="/hosting">View Hosting Plans</Link>
          </Button>
          <Button size="lg" variant="outline" asChild>
            <Link href="/domains">Search Domains</Link>
          </Button>
        </div>
      </div>

      <!-- Features Grid -->
      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-16">
        <Card v-for="feature in features" :key="feature.title" class="text-center">
          <CardHeader>
            <div class="mx-auto w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mb-4">
              <component :is="feature.icon" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <CardTitle class="text-lg">{{ feature.title }}</CardTitle>
          </CardHeader>
          <CardContent>
            <CardDescription>{{ feature.description }}</CardDescription>
          </CardContent>
        </Card>
      </div>

      <!-- Benefits Section -->
      <div class="grid lg:grid-cols-2 gap-12 items-center">
        <div>
          <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-6">
            Why Choose Our Hosting?
          </h2>
          <p class="text-gray-600 dark:text-gray-300 mb-8">
            We provide everything you need to succeed online, from reliable hosting 
            to professional support.
          </p>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="benefit in benefits" :key="benefit" class="flex items-center space-x-3">
              <CheckCircle class="w-5 h-5 text-green-500 flex-shrink-0" />
              <span class="text-gray-700 dark:text-gray-300">{{ benefit }}</span>
            </div>
          </div>
        </div>
        
        <Card class="lg:ml-8">
          <CardHeader>
            <CardTitle class="flex items-center space-x-2">
              <Star class="w-5 h-5 text-yellow-500" />
              <span>Customer Testimonial</span>
            </CardTitle>
          </CardHeader>
          <CardContent>
            <blockquote class="italic text-gray-600 dark:text-gray-300 mb-4">
              "Excellent service and support. Our website has been running smoothly 
              for over a year with zero downtime. Highly recommended!"
            </blockquote>
            <cite class="font-semibold text-gray-900 dark:text-white">- Sarah Johnson, Business Owner</cite>
          </CardContent>
        </Card>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-blue-600 dark:bg-blue-700 py-16">
      <div class="container mx-auto px-6 text-center">
        <h2 class="text-3xl font-bold text-white mb-6">
          Ready to Get Started?
        </h2>
        <p class="text-blue-100 mb-8 max-w-2xl mx-auto">
          Join thousands of satisfied customers who trust us with their web hosting needs.
        </p>
        <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
          <Button size="lg" variant="secondary" asChild>
            <Link href="/customer/register">Create Account</Link>
          </Button>
          <Button size="lg" variant="outline" class="text-white border-white hover:bg-white hover:text-blue-600" asChild>
            <Link href="/hosting">View Plans</Link>
          </Button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-8">
      <div class="container mx-auto px-6">
        <div class="flex flex-col md:flex-row justify-between items-center">
          <div class="flex items-center space-x-2 mb-4 md:mb-0">
            <img src="/1.png" alt="WebSweetStudio" class="w-8 h-8 object-contain" />
            <span class="text-xl font-bold text-white">Ws.</span>
          </div>
          <div class="flex space-x-6 text-sm">
            <Link href="/hosting" class="hover:text-white">Hosting</Link>
            <Link href="/domains" class="hover:text-white">Domains</Link>
            <Link href="/customer/login" class="hover:text-white">Customer Login</Link>
            <Link href="/login" class="hover:text-white">Admin Login</Link>
          </div>
        </div>
        <div class="border-t border-gray-700 mt-6 pt-6 text-center text-sm">
          <p>&copy; 2024 WebSweetStudio. All rights reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>