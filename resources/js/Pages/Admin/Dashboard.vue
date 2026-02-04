<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({
  stats: Object,
});
</script>

<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600 mt-1">HomeMessage Admin Paneli</p>
      </div>
      <div class="mt-4 md:mt-0 flex gap-3">
        <button class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
          O'tgan 7 kun
        </button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
          Yangilash
        </button>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Stat Card 1 -->
      <div class="bg-white rounded-lg shadow border border-gray-200 p-6 hover:shadow-lg transition">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Jami Foydalanuvchi</p>
            <p class="text-4xl font-bold text-gray-900 mt-3">{{ stats.total_users || 0 }}</p>
            <p class="text-green-600 text-sm mt-2">+12% o'tgan oydan</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m6 0a6 6 0 11-12 0 6 6 0 0112 0z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Stat Card 2 -->
      <div class="bg-white rounded-lg shadow border border-gray-200 p-6 hover:shadow-lg transition">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Admin Sonni</p>
            <p class="text-4xl font-bold text-gray-900 mt-3">{{ stats.total_admins || 0 }}</p>
            <p class="text-gray-500 text-sm mt-2">Faol xodimlar</p>
          </div>
          <div class="p-3 bg-purple-100 rounded-lg">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.856-1.487M15 10h.01M13 6h8M6 10a4 4 0 11-8 0 4 4 0 018 0zM6 20v-2a4 4 0 014-4h4a4 4 0 014 4v2"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Stat Card 3 -->
      <div class="bg-white rounded-lg shadow border border-gray-200 p-6 hover:shadow-lg transition">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Jami Massage Turlari</p>
            <p class="text-4xl font-bold text-gray-900 mt-3">{{ stats.total_service_types || 0 }}</p>
            <p class="text-green-600 text-sm mt-2">Mavjud xizmalar</p>
          </div>
          <div class="p-3 bg-green-100 rounded-lg">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
          </div>
        </div>
      </div>

      <!-- Stat Card 4 -->
      <div class="bg-white rounded-lg shadow border border-gray-200 p-6 hover:shadow-lg transition">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <p class="text-gray-600 text-sm font-medium uppercase tracking-wide">Faol Xizmalar</p>
            <p class="text-4xl font-bold text-gray-900 mt-3">{{ stats.active_service_types || 0 }}</p>
            <p class="text-blue-600 text-sm mt-2">Faaliyatda</p>
          </div>
          <div class="p-3 bg-yellow-100 rounded-lg">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Welcome Section -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow border border-gray-200 p-8">
        <div class="flex items-start">
          <div class="flex-1">
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Xush Kelibsiz! 👋</h2>
            <p class="text-gray-600 leading-relaxed">
              HomeMessage admin paneliga xush kelibsiz. Bu platformada siz massage xizmatlari, foydalanuvchilar,
              buyurtmalar va to'lovlarni to'liq boshqara olasiz.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
              <a href="#" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                Hujjat o'qing
              </a>
              <a href="#" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition text-sm font-medium">
                Qo'llab-quvvat
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="bg-white rounded-lg shadow border border-gray-200 p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tez Harakatlar</h3>
        <div class="space-y-2">
          <a href="/admin/service-types" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition text-gray-700 hover:text-blue-600">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Yangi xizmat qo'sh
          </a>
          <a href="/admin/service-types" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition text-gray-700 hover:text-blue-600">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
            </svg>
            Xizmalarni ko'rish
          </a>
          <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition text-gray-700 hover:text-blue-600">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            Hisobotlar
          </a>
          <a href="#" class="flex items-center p-3 rounded-lg hover:bg-blue-50 transition text-gray-700 hover:text-blue-600">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Sozlamalar
          </a>
        </div>
      </div>
    </div>

    <!-- Activity Section -->
    <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <h3 class="text-lg font-bold text-gray-900">So'nggi Faoliyat</h3>
      </div>
      <div class="divide-y divide-gray-200">
        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">Yangi massage turi qo'shildi</p>
              <p class="text-xs text-gray-500 mt-1">2 soat oldin</p>
            </div>
          </div>
          <span class="px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Yangi</span>
        </div>
        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">Xizmat yangilandi</p>
              <p class="text-xs text-gray-500 mt-1">4 soat oldin</p>
            </div>
          </div>
          <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Tasdiqland</span>
        </div>
        <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition">
          <div class="flex items-center">
            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-900">Buyurtma kutilmoqda</p>
              <p class="text-xs text-gray-500 mt-1">1 kun oldin</p>
            </div>
          </div>
          <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">Kutilmoqda</span>
        </div>
      </div>
      <div class="px-6 py-4 bg-gray-50 text-center border-t border-gray-200">
        <a href="#" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Barcha faoliyatni ko'rish →</a>
      </div>
    </div>
  </div>
</template>
