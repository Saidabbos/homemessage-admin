<script setup>
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  dispatcher: Object,
});

const deleteDispatcher = () => {
  if (confirm(t('dispatchers.confirmDelete'))) {
    router.delete(route('admin.dispatchers.destroy', props.dispatcher.id));
  }
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ dispatcher.name }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('dispatchers.info') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link href="/admin/dispatchers" class="text-[#007bff]">{{ t('dispatchers.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.view') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Profile Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-[#1f2d3d] flex items-center">
              <svg class="w-5 h-5 mr-2 text-[#6f42c1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ t('dispatchers.personalInfo') }}
            </h3>
            <span
              :class="['inline-flex items-center px-2 py-1 text-xs font-medium rounded', dispatcher.status ? 'bg-[#d4edda] text-[#155724]' : 'bg-[#f8d7da] text-[#721c24]']"
            >
              {{ dispatcher.status ? t('common.active') : t('common.inactive') }}
            </span>
          </div>
          <div class="p-4">
            <div class="flex items-center mb-6">
              <div class="w-16 h-16 rounded-full bg-[#6f42c1] flex items-center justify-center text-white text-2xl font-semibold">
                {{ dispatcher.name.charAt(0).toUpperCase() }}
              </div>
              <div class="ml-4">
                <h4 class="text-lg font-semibold text-[#1f2d3d]">{{ dispatcher.name }}</h4>
                <p class="text-sm text-[#6c757d]">{{ t('dispatchers.singular') }}</p>
              </div>
            </div>

            <div class="space-y-2 text-sm">
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-[#6c757d]">{{ t('dispatchers.email') }}:</span>
                <a :href="`mailto:${dispatcher.email}`" class="text-[#007bff] hover:underline">{{ dispatcher.email }}</a>
              </div>
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-[#6c757d]">{{ t('dispatchers.phone') }}:</span>
                <span v-if="dispatcher.phone" class="font-medium text-[#1f2d3d]">
                  <a :href="`tel:${dispatcher.phone}`" class="text-[#007bff] hover:underline">{{ dispatcher.phone }}</a>
                </span>
                <span v-else class="text-[#6c757d]">-</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-[#6c757d]">{{ t('common.id') }}:</span>
                <span class="font-medium text-[#1f2d3d]">#{{ dispatcher.id }}</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-[#6c757d]">{{ t('common.createdAt') }}:</span>
                <span class="font-medium text-[#1f2d3d]">{{ formatDate(dispatcher.created_at) }}</span>
              </div>
              <div class="flex items-center justify-between py-2">
                <span class="text-[#6c757d]">{{ t('common.updatedAt') }}:</span>
                <span class="font-medium text-[#1f2d3d]">{{ formatDate(dispatcher.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-4">
        <!-- Actions Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 bg-[#17a2b8] rounded-t">
            <h3 class="font-semibold text-white">{{ t('common.actions') }}</h3>
          </div>
          <div class="p-4 space-y-2">
            <Link
              :href="route('admin.dispatchers.edit', dispatcher.id)"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#ffc107] text-[#1f2d3d] text-sm font-medium rounded hover:bg-[#e0a800] transition"
            >
              {{ t('common.edit') }}
            </Link>
            <Link
              href="/admin/dispatchers"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#6c757d] text-white text-sm font-medium rounded hover:bg-[#5a6268] transition"
            >
              {{ t('common.back') }}
            </Link>
            <button
              @click="deleteDispatcher"
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition"
            >
              {{ t('common.delete') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
