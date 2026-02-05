<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

defineProps({
  masters: Object,
});

const getTranslation = (item, field) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field][locale.value] || item[field]['uz'] || Object.values(item[field])[0] || '';
};

const deleteMaster = (id) => {
  if (confirm(t('common.confirmDelete'))) {
    router.delete(route('admin.masters.destroy', id));
  }
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('masters.title') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('masters.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('masters.title') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow-sm">
      <!-- Card Header -->
      <div class="px-4 py-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="font-semibold text-[#1f2d3d] flex items-center">
          <svg class="w-5 h-5 mr-2 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          {{ t('masters.list') }}
        </h3>
        <Link
          href="/admin/masters/create"
          class="inline-flex items-center px-4 py-2 bg-[#28a745] text-white text-sm font-medium rounded hover:bg-[#218838] transition"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          {{ t('common.addNew') }}
        </Link>
      </div>

      <!-- Card Body -->
      <div class="p-0">
        <!-- Table -->
        <div v-if="masters.data.length > 0" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-[#f8f9fa] border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">#</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('common.image') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('masters.fullName') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('masters.phone') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('masters.experience') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('masters.rating') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('common.status') }}</th>
                <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">{{ t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(master, index) in masters.data" :key="master.id" class="hover:bg-[#f8f9fa] transition">
                <td class="px-4 py-3 text-[#6c757d]">{{ index + 1 }}</td>
                <td class="px-4 py-3">
                  <img
                    :src="master.photo_url"
                    :alt="master.full_name"
                    class="w-10 h-10 rounded-full object-cover border-2 border-gray-200"
                  />
                </td>
                <td class="px-4 py-3">
                  <div class="font-medium text-[#1f2d3d]">{{ master.full_name }}</div>
                  <div class="text-xs text-[#6c757d]">{{ master.gender === 'male' ? t('masters.male') : t('masters.female') }}</div>
                </td>
                <td class="px-4 py-3">
                  <a :href="'tel:' + master.phone" class="text-[#007bff] hover:underline">{{ master.phone }}</a>
                </td>
                <td class="px-4 py-3">
                  <span class="text-[#1f2d3d]">{{ master.experience_years }} {{ t('masters.years') }}</span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <svg class="w-4 h-4 text-[#ffc107] mr-1" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    <span class="font-medium text-[#1f2d3d]">{{ master.rating }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span
                    v-if="master.status"
                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#d4edda] text-[#155724]"
                  >
                    <span class="w-1.5 h-1.5 bg-[#28a745] rounded-full mr-1.5"></span>
                    {{ t('common.active') }}
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#f8d7da] text-[#721c24]"
                  >
                    <span class="w-1.5 h-1.5 bg-[#dc3545] rounded-full mr-1.5"></span>
                    {{ t('common.inactive') }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-1">
                    <Link
                      :href="route('admin.masters.show', master.id)"
                      class="p-1.5 text-[#17a2b8] hover:bg-[#17a2b8] hover:text-white rounded transition"
                      :title="t('common.view')"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <Link
                      :href="route('admin.masters.edit', master.id)"
                      class="p-1.5 text-[#ffc107] hover:bg-[#ffc107] hover:text-[#1f2d3d] rounded transition"
                      :title="t('common.edit')"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button
                      @click="deleteMaster(master.id)"
                      class="p-1.5 text-[#dc3545] hover:bg-[#dc3545] hover:text-white rounded transition"
                      :title="t('common.delete')"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
          <div class="w-16 h-16 bg-[#e9ecef] rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-[#6c757d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-[#1f2d3d] mb-2">{{ t('common.noData') }}</h3>
          <p class="text-[#6c757d] mb-4">{{ t('masters.emptyMessage') }}</p>
          <Link
            href="/admin/masters/create"
            class="inline-flex items-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ t('masters.addFirst') }}
          </Link>
        </div>
      </div>

      <!-- Card Footer with Pagination -->
      <div v-if="masters.data.length > 0" class="px-4 py-3 bg-[#f8f9fa] border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-sm text-[#6c757d]">
            {{ t('common.total') }}: <strong>{{ masters.total }}</strong> {{ t('common.records') }}
          </div>
          <Pagination :links="masters.links" />
        </div>
      </div>
    </div>
  </div>
</template>
