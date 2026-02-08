<script setup>
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
  pressureLevel: Object,
});

const deletePressureLevel = () => {
  if (confirm('Haqiqatan ham bu bosim darajasini o\'chirmoqchimisiz?')) {
    router.delete(route('admin.pressure-levels.destroy', props.pressureLevel.id));
  }
};

const getTranslation = (key, locale) => {
  return props.pressureLevel[locale]?.[key] || '-';
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ pressureLevel.uz?.name || pressureLevel.en?.name }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">Bosim darajasi ma'lumotlari</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">Bosh sahifa</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/pressure-levels" class="text-[#007bff]">Bosim Darajasi</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">Ko'rish</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <div class="lg:col-span-2 space-y-4">
        <!-- Info Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-[#1f2d3d]">Asosiy Ma'lumotlar</h3>
            <span :class="[
              'inline-flex items-center px-2 py-1 text-xs font-medium rounded',
              pressureLevel.status ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]'
            ]">
              <span :class="['w-1.5 h-1.5 rounded-full mr-1.5', pressureLevel.status ? 'bg-[#28a745]' : 'bg-[#dc3545]']"></span>
              {{ pressureLevel.status ? 'Faol' : 'Nofaol' }}
            </span>
          </div>
          <div class="p-4 space-y-4">
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-[#6c757d]">Slug:</span>
              <code class="bg-[#f8f9fa] px-2 py-1 rounded text-[#1f2d3d]">{{ pressureLevel.slug }}</code>
            </div>
            <div class="flex items-center justify-between py-2 border-b border-gray-100">
              <span class="text-[#6c757d]">Tartib:</span>
              <span class="font-medium text-[#1f2d3d]">{{ pressureLevel.sort_order }}</span>
            </div>
            <div class="flex items-center justify-between py-2">
              <span class="text-[#6c757d]">ID:</span>
              <span class="font-medium text-[#1f2d3d]">#{{ pressureLevel.id }}</span>
            </div>
          </div>
        </div>

        <!-- Descriptions Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">Tasvirlar</h3>
          </div>
          <div class="p-4 space-y-6">
            <div>
              <h4 class="text-sm font-semibold text-[#1f2d3d] mb-2">O'zbek</h4>
              <p v-if="pressureLevel.uz?.name" class="text-[#1f2d3d]">{{ pressureLevel.uz.name }}</p>
              <p v-if="pressureLevel.uz?.description" class="text-[#6c757d] text-sm mt-2">{{ pressureLevel.uz.description }}</p>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-[#1f2d3d] mb-2">Русский</h4>
              <p v-if="pressureLevel.ru?.name" class="text-[#1f2d3d]">{{ pressureLevel.ru.name }}</p>
              <p v-if="pressureLevel.ru?.description" class="text-[#6c757d] text-sm mt-2">{{ pressureLevel.ru.description }}</p>
            </div>
            <div>
              <h4 class="text-sm font-semibold text-[#1f2d3d] mb-2">English</h4>
              <p v-if="pressureLevel.en?.name" class="text-[#1f2d3d]">{{ pressureLevel.en.name }}</p>
              <p v-if="pressureLevel.en?.description" class="text-[#6c757d] text-sm mt-2">{{ pressureLevel.en.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-4">
        <!-- Actions Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8] rounded-t">
            <h3 class="font-semibold text-white">Amallar</h3>
          </div>
          <div class="p-4 space-y-2">
            <Link
              :href="route('admin.pressure-levels.edit', pressureLevel.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              Tahrirlash
            </Link>
            <Link
              href="/admin/pressure-levels"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
              </svg>
              Orqaga
            </Link>
            <button
              @click="deletePressureLevel"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              O'chirish
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
