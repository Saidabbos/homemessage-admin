<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import LanguageSwitcher from '@/Components/Admin/LanguageSwitcher.vue';

defineProps({
  user: Object,
});

const showDropdown = ref(false);
const showNotifications = ref(false);
const showMessages = ref(false);

const logout = () => {
  router.post(route('admin.logout'));
};
</script>

<template>
  <header class="h-[57px] bg-white border-b border-gray-200 flex items-center justify-between px-4 sticky top-0 z-40">
    <!-- Left side -->
    <div class="flex items-center gap-4">
      <!-- Sidebar toggle (mobile) -->
      <button class="lg:hidden p-2 text-gray-600 hover:bg-gray-100 rounded">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
      </button>

      <!-- Breadcrumb / Navigation -->
      <nav class="hidden md:flex items-center gap-2 text-sm">
        <Link href="/admin/dashboard" class="text-[#007bff] hover:text-[#0056b3]">Bosh sahifa</Link>
        <span class="text-gray-400">/</span>
        <span class="text-gray-600">Dashboard</span>
      </nav>
    </div>

    <!-- Right side -->
    <div class="flex items-center gap-1">
      <!-- Search -->
      <button class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
      </button>

      <!-- Messages -->
      <div class="relative">
        <button
          @click="showMessages = !showMessages"
          class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition relative"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
          </svg>
          <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-[#dc3545] text-white text-[10px] rounded-full flex items-center justify-center font-bold">3</span>
        </button>

        <!-- Messages Dropdown -->
        <div
          v-if="showMessages"
          class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="px-4 py-3 border-b border-gray-200 bg-[#007bff] text-white rounded-t-lg">
            <span class="font-semibold">3 ta Xabar</span>
          </div>
          <div class="max-h-64 overflow-y-auto">
            <a href="#" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
              <div class="w-10 h-10 rounded-full bg-[#17a2b8] flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">J</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">Jamshid</p>
                <p class="text-xs text-gray-500 truncate">Buyurtma haqida savol...</p>
                <p class="text-xs text-[#6c757d] mt-1">4 daqiqa oldin</p>
              </div>
            </a>
            <a href="#" class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
              <div class="w-10 h-10 rounded-full bg-[#28a745] flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">F</div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">Fatima</p>
                <p class="text-xs text-gray-500 truncate">Yangi xizmat qo'shildi</p>
                <p class="text-xs text-[#6c757d] mt-1">1 soat oldin</p>
              </div>
            </a>
          </div>
          <a href="#" class="block px-4 py-3 text-center text-sm text-[#007bff] hover:bg-gray-50 border-t border-gray-200 rounded-b-lg">
            Barcha xabarlarni ko'rish
          </a>
        </div>
      </div>

      <!-- Notifications -->
      <div class="relative">
        <button
          @click="showNotifications = !showNotifications"
          class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition relative"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-[#ffc107] text-[#1f2d3d] text-[10px] rounded-full flex items-center justify-center font-bold">5</span>
        </button>

        <!-- Notifications Dropdown -->
        <div
          v-if="showNotifications"
          class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="px-4 py-3 border-b border-gray-200 bg-white">
            <span class="font-semibold text-gray-900">5 ta Bildirishnoma</span>
          </div>
          <div class="max-h-64 overflow-y-auto">
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
              <div class="w-8 h-8 rounded-full bg-[#17a2b8] flex items-center justify-center text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-700">Yangi foydalanuvchi ro'yxatdan o'tdi</p>
                <p class="text-xs text-[#6c757d]">3 daqiqa oldin</p>
              </div>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
              <div class="w-8 h-8 rounded-full bg-[#28a745] flex items-center justify-center text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-700">Buyurtma tasdiqlandi</p>
                <p class="text-xs text-[#6c757d]">15 daqiqa oldin</p>
              </div>
            </a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50">
              <div class="w-8 h-8 rounded-full bg-[#ffc107] flex items-center justify-center text-[#1f2d3d]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-700">Yangi shikoyat keldi</p>
                <p class="text-xs text-[#6c757d]">1 soat oldin</p>
              </div>
            </a>
          </div>
          <a href="#" class="block px-4 py-3 text-center text-sm text-[#007bff] hover:bg-gray-50 border-t border-gray-200 rounded-b-lg">
            Barcha bildirishnomalar
          </a>
        </div>
      </div>

      <!-- Language Switcher -->
      <LanguageSwitcher />

      <!-- Fullscreen -->
      <button class="hidden md:block p-2 text-gray-500 hover:bg-gray-100 rounded-lg transition">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
        </svg>
      </button>

      <!-- User Menu -->
      <div class="relative ml-2">
        <button
          @click="showDropdown = !showDropdown"
          class="flex items-center gap-2 px-3 py-1.5 hover:bg-gray-100 rounded-lg transition"
        >
          <div class="w-8 h-8 rounded-full bg-[#007bff] flex items-center justify-center text-white text-sm font-semibold">
            {{ user?.avatar || 'A' }}
          </div>
          <span class="hidden md:block text-sm text-gray-700">{{ user?.name || 'Admin' }}</span>
          <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
          </svg>
        </button>

        <!-- User Dropdown -->
        <div
          v-if="showDropdown"
          class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
        >
          <div class="px-4 py-3 border-b border-gray-200">
            <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
            <p class="text-xs text-gray-500">{{ user?.email || 'admin@example.com' }}</p>
          </div>
          <div class="py-1">
            <Link href="/admin/profile" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              <svg class="w-4 h-4 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              Profil
            </Link>
            <Link href="/admin/settings" class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
              <svg class="w-4 h-4 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              Sozlamalar
            </Link>
          </div>
          <div class="border-t border-gray-200">
            <button
              @click="logout"
              class="flex items-center gap-3 w-full px-4 py-2 text-sm text-[#dc3545] hover:bg-gray-50"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
              </svg>
              Chiqish
            </button>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>
