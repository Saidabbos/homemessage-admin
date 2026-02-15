<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { computed } from 'vue';

const { t } = useI18n();
const page = usePage();

const user = computed(() => page.props.auth?.user);
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Header -->
    <header class="bg-purple-700 text-white sticky top-0 z-50">
      <div class="max-w-2xl mx-auto px-4 py-3">
        <div class="flex items-center justify-between">
          <Link href="/customer/dashboard" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
              </svg>
            </div>
            <span class="font-bold text-lg">Home Massage</span>
          </Link>
          
          <div class="flex items-center gap-3">
            <span v-if="user" class="text-sm text-purple-200">{{ user.name }}</span>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="pb-20">
      <slot />
    </main>

    <!-- Bottom Navigation -->
    <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
      <div class="max-w-2xl mx-auto px-4">
        <div class="flex items-center justify-around py-2">
          <Link
            href="/customer/dashboard"
            class="flex flex-col items-center gap-1 px-4 py-2 text-gray-600 hover:text-purple-600 transition-colors"
            :class="{ 'text-purple-600': $page.url === '/customer/dashboard' }"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-xs">{{ t('customer.home') }}</span>
          </Link>
          
          <Link
            href="/customer/orders"
            class="flex flex-col items-center gap-1 px-4 py-2 text-gray-600 hover:text-purple-600 transition-colors"
            :class="{ 'text-purple-600': $page.url.startsWith('/customer/orders') }"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="text-xs">{{ t('customer.orders') }}</span>
          </Link>
          
          <Link
            href="/"
            class="flex flex-col items-center gap-1 px-4 py-2"
          >
            <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center -mt-6 shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
              </svg>
            </div>
            <span class="text-xs text-gray-600">{{ t('orders.book') }}</span>
          </Link>
          
          <Link
            href="/customer/dashboard"
            class="flex flex-col items-center gap-1 px-4 py-2 text-gray-600 hover:text-purple-600 transition-colors"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            <span class="text-xs">{{ t('customer.profile') }}</span>
          </Link>
        </div>
      </div>
    </nav>
  </div>
</template>
