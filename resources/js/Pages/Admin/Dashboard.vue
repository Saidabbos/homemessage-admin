<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: AdminLayout });

defineProps({
  stats: Object,
});
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">Dashboard</h1>
          <p class="text-sm text-[#6c757d] mt-1">Boshqaruv paneli</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><a href="#" class="text-[#007bff]">Bosh sahifa</a></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">Dashboard</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Small Boxes (Stat boxes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
      <!-- Info Box 1 - Blue -->
      <div class="bg-[#17a2b8] rounded shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-white">{{ stats?.total_users || 0 }}</p>
              <p class="text-white/80 text-sm">Jami Foydalanuvchi</p>
            </div>
            <div class="text-white/30">
              <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
          </div>
        </div>
        <a href="#" class="block bg-black/10 text-white text-center text-sm py-2 hover:bg-black/20 transition">
          Batafsil <span class="ml-1">→</span>
        </a>
      </div>

      <!-- Info Box 2 - Green -->
      <div class="bg-[#28a745] rounded shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-white">{{ stats?.total_admins || 0 }}</p>
              <p class="text-white/80 text-sm">Adminlar</p>
            </div>
            <div class="text-white/30">
              <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
              </svg>
            </div>
          </div>
        </div>
        <a href="#" class="block bg-black/10 text-white text-center text-sm py-2 hover:bg-black/20 transition">
          Batafsil <span class="ml-1">→</span>
        </a>
      </div>

      <!-- Info Box 3 - Yellow -->
      <div class="bg-[#ffc107] rounded shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-[#1f2d3d]">{{ stats?.total_service_types || 0 }}</p>
              <p class="text-[#1f2d3d]/70 text-sm">Massage Turlari</p>
            </div>
            <div class="text-[#1f2d3d]/20">
              <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
              </svg>
            </div>
          </div>
        </div>
        <Link href="/admin/service-types" class="block bg-black/10 text-[#1f2d3d] text-center text-sm py-2 hover:bg-black/20 transition">
          Batafsil <span class="ml-1">→</span>
        </Link>
      </div>

      <!-- Info Box 4 - Red -->
      <div class="bg-[#dc3545] rounded shadow-sm overflow-hidden">
        <div class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-3xl font-bold text-white">{{ stats?.active_service_types || 0 }}</p>
              <p class="text-white/80 text-sm">Faol Xizmatlar</p>
            </div>
            <div class="text-white/30">
              <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
              </svg>
            </div>
          </div>
        </div>
        <a href="#" class="block bg-black/10 text-white text-center text-sm py-2 hover:bg-black/20 transition">
          Batafsil <span class="ml-1">→</span>
        </a>
      </div>
    </div>

    <!-- Main Content Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
      <!-- Card - Welcome -->
      <div class="lg:col-span-2 bg-white rounded shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
          <h3 class="font-semibold text-[#1f2d3d]">
            <svg class="w-4 h-4 inline mr-2 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Xush Kelibsiz!
          </h3>
          <div class="flex gap-1">
            <button class="p-1 text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
              </svg>
            </button>
            <button class="p-1 text-gray-400 hover:text-gray-600">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
        <div class="p-4">
          <div class="flex items-start gap-4">
            <div class="w-16 h-16 bg-[#17a2b8] rounded-lg flex items-center justify-center text-white text-2xl flex-shrink-0">
              HM
            </div>
            <div>
              <h4 class="text-lg font-semibold text-[#1f2d3d] mb-2">HomeMessage Admin Paneli</h4>
              <p class="text-[#6c757d] text-sm leading-relaxed mb-4">
                HomeMessage admin paneliga xush kelibsiz. Bu platformada siz massage xizmatlari, foydalanuvchilar,
                buyurtmalar va to'lovlarni to'liq boshqara olasiz. Chap tomondagi menyu orqali kerakli bo'limga o'ting.
              </p>
              <div class="flex flex-wrap gap-2">
                <a href="#" class="inline-flex items-center px-3 py-1.5 bg-[#007bff] text-white text-sm rounded hover:bg-[#0069d9] transition">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                  </svg>
                  Hujjat
                </a>
                <a href="#" class="inline-flex items-center px-3 py-1.5 bg-[#6c757d] text-white text-sm rounded hover:bg-[#5a6268] transition">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                  </svg>
                  Yordam
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Card - Quick Actions -->
      <div class="bg-white rounded shadow-sm">
        <div class="px-4 py-3 border-b border-gray-200 bg-[#007bff] rounded-t">
          <h3 class="font-semibold text-white">
            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Tez Harakatlar
          </h3>
        </div>
        <div class="p-0">
          <Link href="/admin/service-types/create" class="flex items-center px-4 py-3 text-[#1f2d3d] hover:bg-[#f8f9fa] border-b border-gray-100 transition">
            <span class="w-8 h-8 bg-[#17a2b8] text-white rounded flex items-center justify-center mr-3">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </span>
            <span class="text-sm">Yangi xizmat qo'shish</span>
          </Link>
          <Link href="/admin/service-types" class="flex items-center px-4 py-3 text-[#1f2d3d] hover:bg-[#f8f9fa] border-b border-gray-100 transition">
            <span class="w-8 h-8 bg-[#28a745] text-white rounded flex items-center justify-center mr-3">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
              </svg>
            </span>
            <span class="text-sm">Xizmalar ro'yxati</span>
          </Link>
          <a href="#" class="flex items-center px-4 py-3 text-[#1f2d3d] hover:bg-[#f8f9fa] border-b border-gray-100 transition">
            <span class="w-8 h-8 bg-[#ffc107] text-[#1f2d3d] rounded flex items-center justify-center mr-3">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </span>
            <span class="text-sm">Hisobotlar</span>
          </a>
          <a href="#" class="flex items-center px-4 py-3 text-[#1f2d3d] hover:bg-[#f8f9fa] transition">
            <span class="w-8 h-8 bg-[#dc3545] text-white rounded flex items-center justify-center mr-3">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </span>
            <span class="text-sm">Sozlamalar</span>
          </a>
        </div>
      </div>
    </div>

    <!-- Recent Activity Card -->
    <div class="bg-white rounded shadow-sm">
      <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
        <h3 class="font-semibold text-[#1f2d3d]">
          <svg class="w-4 h-4 inline mr-2 text-[#6c757d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          So'nggi Faoliyat
        </h3>
        <span class="text-xs bg-[#007bff] text-white px-2 py-1 rounded">Yangi</span>
      </div>
      <div class="p-0">
        <!-- Timeline -->
        <div class="relative">
          <!-- Timeline Item 1 -->
          <div class="flex items-start px-4 py-3 border-b border-gray-100 hover:bg-[#f8f9fa] transition">
            <div class="w-8 h-8 bg-[#17a2b8] rounded-full flex items-center justify-center text-white flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm text-[#1f2d3d]">
                <strong>Yangi xizmat</strong> qo'shildi - "Thai Massage"
              </p>
              <p class="text-xs text-[#6c757d] mt-1">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                2 soat oldin
              </p>
            </div>
            <span class="text-xs bg-[#17a2b8] text-white px-2 py-0.5 rounded">Yangi</span>
          </div>

          <!-- Timeline Item 2 -->
          <div class="flex items-start px-4 py-3 border-b border-gray-100 hover:bg-[#f8f9fa] transition">
            <div class="w-8 h-8 bg-[#28a745] rounded-full flex items-center justify-center text-white flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm text-[#1f2d3d]">
                <strong>Buyurtma #1234</strong> tasdiqlandi
              </p>
              <p class="text-xs text-[#6c757d] mt-1">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                4 soat oldin
              </p>
            </div>
            <span class="text-xs bg-[#28a745] text-white px-2 py-0.5 rounded">Tasdiqlandi</span>
          </div>

          <!-- Timeline Item 3 -->
          <div class="flex items-start px-4 py-3 border-b border-gray-100 hover:bg-[#f8f9fa] transition">
            <div class="w-8 h-8 bg-[#ffc107] rounded-full flex items-center justify-center text-[#1f2d3d] flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm text-[#1f2d3d]">
                <strong>Yangi foydalanuvchi</strong> ro'yxatdan o'tdi
              </p>
              <p class="text-xs text-[#6c757d] mt-1">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                1 kun oldin
              </p>
            </div>
            <span class="text-xs bg-[#ffc107] text-[#1f2d3d] px-2 py-0.5 rounded">Kutilmoqda</span>
          </div>

          <!-- Timeline Item 4 -->
          <div class="flex items-start px-4 py-3 hover:bg-[#f8f9fa] transition">
            <div class="w-8 h-8 bg-[#6c757d] rounded-full flex items-center justify-center text-white flex-shrink-0">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </div>
            <div class="ml-3 flex-1">
              <p class="text-sm text-[#1f2d3d]">
                <strong>Xizmat ma'lumotlari</strong> yangilandi
              </p>
              <p class="text-xs text-[#6c757d] mt-1">
                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                2 kun oldin
              </p>
            </div>
            <span class="text-xs bg-[#6c757d] text-white px-2 py-0.5 rounded">Arxiv</span>
          </div>
        </div>
      </div>
      <div class="px-4 py-3 bg-[#f8f9fa] border-t border-gray-200 text-center rounded-b">
        <a href="#" class="text-sm text-[#007bff] hover:text-[#0056b3]">
          Barcha faoliyatni ko'rish →
        </a>
      </div>
    </div>
  </div>
</template>
