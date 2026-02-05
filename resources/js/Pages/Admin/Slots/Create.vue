<script setup>
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const form = useForm({
  start_time: '',
  end_time: '',
  sort_order: 0,
  is_active: true,
});

const submit = () => {
  form.post(route('admin.slots.store'));
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('slots.create') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('slots.createSubtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link :href="route('admin.slots.index')" class="text-[#007bff]">{{ t('slots.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('common.create') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded shadow-sm">
      <!-- Card Header -->
      <div class="px-4 py-3 border-b border-gray-200 bg-[#28a745]">
        <h3 class="font-semibold text-white flex items-center">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ t('slots.newSlot') }}
        </h3>
      </div>

      <!-- Card Body -->
      <form @submit.prevent="submit" class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Start Time -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
              {{ t('slots.startTime') }} <span class="text-[#dc3545]">*</span>
            </label>
            <input
              type="time"
              v-model="form.start_time"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              :class="{ 'border-[#dc3545]': form.errors.start_time }"
            />
            <p v-if="form.errors.start_time" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.start_time }}</p>
          </div>

          <!-- End Time -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
              {{ t('slots.endTime') }} <span class="text-[#dc3545]">*</span>
            </label>
            <input
              type="time"
              v-model="form.end_time"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              :class="{ 'border-[#dc3545]': form.errors.end_time }"
            />
            <p v-if="form.errors.end_time" class="mt-1 text-sm text-[#dc3545]">{{ form.errors.end_time }}</p>
          </div>

          <!-- Sort Order -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
              {{ t('slots.sortOrder') }}
            </label>
            <input
              type="number"
              v-model="form.sort_order"
              min="0"
              max="255"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            />
            <p class="mt-1 text-xs text-[#6c757d]">{{ t('slots.sortOrderHint') }}</p>
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">
              {{ t('common.status') }}
            </label>
            <label class="inline-flex items-center mt-2">
              <input
                type="checkbox"
                v-model="form.is_active"
                class="rounded border-gray-300 text-[#007bff] focus:ring-[#007bff]"
              />
              <span class="ml-2 text-sm text-[#1f2d3d]">{{ t('common.active') }}</span>
            </label>
          </div>
        </div>

        <!-- Card Footer -->
        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
          <Link
            :href="route('admin.slots.index')"
            class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition"
          >
            {{ t('common.cancel') }}
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-[#28a745] text-white text-sm font-medium rounded hover:bg-[#218838] transition disabled:opacity-50"
          >
            <span v-if="form.processing">{{ t('common.saving') }}...</span>
            <span v-else>{{ t('common.save') }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
