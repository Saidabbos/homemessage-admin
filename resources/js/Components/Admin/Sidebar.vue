<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

const isActive = (path) => {
  const url = page.url;
  if (path === 'admin.dashboard') {
    return url === '/admin/dashboard';
  }
  // Convert route name to URL path: admin.oils -> /admin/oils
  const urlPath = '/' + path.replace(/\./g, '/');
  return url.startsWith(urlPath);
};

const menuOpen = ref({
  services: false,
  users: false,
  reports: false,
});

const toggleMenu = (menu) => {
  menuOpen.value[menu] = !menuOpen.value[menu];
};
</script>

<template>
  <aside class="fixed left-0 top-0 h-screen w-[250px] bg-[#343a40] text-[#c2c7d0] flex flex-col z-50 overflow-hidden">
    <!-- Brand Logo -->
    <div class="h-[57px] flex items-center px-4 bg-[#343a40] border-b border-[#4b545c]">
      <Link href="/admin/dashboard" class="flex items-center">
        <span class="text-xl font-bold text-white">
          <span class="text-[#17a2b8]">Home</span>Message
        </span>
      </Link>
    </div>

    <!-- User Panel -->
    <div class="flex items-center px-4 py-3 border-b border-[#4b545c]">
      <div class="w-8 h-8 rounded-full bg-[#17a2b8] flex items-center justify-center text-white text-sm font-semibold">
        A
      </div>
      <div class="ml-3">
        <p class="text-sm text-white leading-tight">Admin User</p>
        <span class="inline-flex items-center text-xs text-[#28a745]">
          <span class="w-2 h-2 bg-[#28a745] rounded-full mr-1"></span>
          {{ t('sidebar.online') }}
        </span>
      </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="flex-1 overflow-y-auto py-2">
      <ul class="space-y-1 px-2">
        <!-- Dashboard -->
        <li>
          <Link
            href="/admin/dashboard"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.dashboard')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            {{ t('sidebar.dashboard') }}
          </Link>
        </li>

        <!-- Xizmatlar Section -->
        <li class="mt-4">
          <p class="px-3 text-xs uppercase text-[#6c757d] font-semibold tracking-wider mb-2">{{ t('sidebar.services') }}</p>
        </li>

        <li>
          <Link
            href="/admin/service-types"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.service-types')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            {{ t('sidebar.massageTypes') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/oils"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.oils')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            {{ t('sidebar.oilTypes') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/standard-items"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.standard-items')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            {{ t('sidebar.standardItems') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/orders"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.orders')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            {{ t('sidebar.allOrders') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/orders/new"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              page.url === '/admin/orders/new'
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ t('sidebar.newOrders') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/testimonials"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.testimonials')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
              <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
            </svg>
            {{ t('sidebar.testimonials') }}
          </Link>
        </li>

        <!-- Foydalanuvchilar Section -->
        <li class="mt-4">
          <p class="px-3 text-xs uppercase text-[#6c757d] font-semibold tracking-wider mb-2">{{ t('sidebar.users') }}</p>
        </li>

        <li>
          <Link
            href="/admin/masters"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.masters')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            {{ t('sidebar.masters') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/dispatchers"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.dispatchers')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{ t('sidebar.dispatchers') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/customers"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.customers')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            {{ t('sidebar.clients') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/ratings"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.ratings')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 24 24">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            {{ t('sidebar.ratings') }}
          </Link>
        </li>

        <!-- Hisobotlar Section -->
        <li class="mt-4">
          <p class="px-3 text-xs uppercase text-[#6c757d] font-semibold tracking-wider mb-2">{{ t('sidebar.reports') }}</p>
        </li>

        <li>
          <Link
            href="/admin/reports"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.reports')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            {{ t('sidebar.ordersReport') }}
          </Link>
        </li>

        <li>
          <Link
            href="/admin/reports/masters"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              page.url === '/admin/reports/masters'
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ t('sidebar.mastersReport') }}
          </Link>
        </li>

        <!-- Sozlamalar -->
        <li class="mt-4">
          <p class="px-3 text-xs uppercase text-[#6c757d] font-semibold tracking-wider mb-2">{{ t('sidebar.system') }}</p>
        </li>

        <li>
          <Link
            href="/admin/settings"
            :class="[
              'flex items-center px-3 py-2 rounded text-sm transition-colors',
              isActive('admin.settings')
                ? 'bg-[#007bff] text-white'
                : 'hover:bg-[#495057]'
            ]"
          >
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ t('sidebar.settings') }}
          </Link>
        </li>
      </ul>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-[#4b545c] text-xs text-[#6c757d] text-center">
      v1.0.0
    </div>
  </aside>
</template>
