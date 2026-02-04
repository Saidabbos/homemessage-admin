<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

defineProps({
  stats: Object,
});
</script>

<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-800 mb-8">📊 Dashboard</h1>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <!-- Total Users -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Jami Foydalanuvchi</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_users }}</p>
          </div>
          <div class="text-4xl text-blue-500">👥</div>
        </div>
      </div>

      <!-- Total Admins -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Admin</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_admins }}</p>
          </div>
          <div class="text-4xl text-purple-500">👨‍💼</div>
        </div>
      </div>

      <!-- Total Service Types -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Massage Turlari</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.total_service_types }}</p>
          </div>
          <div class="text-4xl text-green-500">💆</div>
        </div>
      </div>

      <!-- Active Service Types -->
      <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm font-medium">Faol Massage Turlari</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ stats.active_service_types }}</p>
          </div>
          <div class="text-4xl text-yellow-500">✨</div>
        </div>
      </div>
    </div>

    <!-- Welcome Section -->
    <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg shadow-lg p-8 text-white">
      <h2 class="text-2xl font-bold mb-2">HomeMessage Admin Paneliga Xush Kelibsiz!</h2>
      <p class="text-purple-100 mb-4">
        Bu admin panelida siz massage xizmatlari, foydalanuvchilar va bronlarni boshqarishdiz.
      </p>
      <p class="text-purple-200 text-sm">Boshlash uchun chapda menyu orqali navigatsiya qiling.</p>
    </div>
  </div>
</template>
