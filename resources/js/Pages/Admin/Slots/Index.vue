<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  slots: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const applyFilters = () => {
  router.get(route('admin.slots.index'), {
    search: search.value || undefined,
    status: status.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  status.value = '';
  router.get(route('admin.slots.index'));
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});

watch(status, applyFilters);

const deleteSlot = (id) => {
  if (confirm(t('slots.confirmDelete'))) {
    router.delete(route('admin.slots.destroy', id));
  }
};

const generateDefaults = () => {
  if (confirm(t('slots.confirmGenerate'))) {
    router.post(route('admin.slots.generate-defaults'));
  }
};

const hasActiveFilters = () => search.value || status.value;

const formatTime = (time) => {
  return time ? time.substring(0, 5) : '';
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('slots.title') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('slots.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('slots.title') }}</li>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ t('slots.list') }}
        </h3>
        <div class="flex gap-2">
          <button
            @click="generateDefaults"
            class="inline-flex items-center px-4 py-2 bg-[#17a2b8] text-white text-sm font-medium rounded hover:bg-[#138496] transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ t('slots.generateDefaults') }}
          </button>
          <Link
            href="/admin/slots/create"
            class="inline-flex items-center px-4 py-2 bg-[#28a745] text-white text-sm font-medium rounded hover:bg-[#218838] transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            {{ t('slots.new') }}
          </Link>
        </div>
      </div>

      <!-- Filters -->
      <div class="px-4 py-3 bg-[#f8f9fa] border-b border-gray-200">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="flex-1">
            <input
              type="text"
              v-model="search"
              :placeholder="t('common.search') + '...'"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            />
          </div>
          <div class="sm:w-40">
            <select
              v-model="status"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            >
              <option value="">{{ t('common.allStatuses') }}</option>
              <option value="active">{{ t('common.active') }}</option>
              <option value="inactive">{{ t('common.inactive') }}</option>
            </select>
          </div>
          <button
            v-if="hasActiveFilters()"
            @click="resetFilters"
            class="px-3 py-2 text-sm text-[#6c757d] hover:text-[#1f2d3d] transition"
          >
            {{ t('common.reset') }}
          </button>
        </div>
      </div>

      <!-- Card Body -->
      <div class="p-0">
        <!-- Table -->
        <div v-if="slots.data.length > 0" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-[#f8f9fa] border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">#</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('slots.timeRange') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('slots.startTime') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('slots.endTime') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('common.status') }}</th>
                <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">{{ t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(slot, index) in slots.data" :key="slot.id" class="hover:bg-[#f8f9fa] transition">
                <td class="px-4 py-3 text-[#6c757d]">{{ slot.sort_order || index + 1 }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-3 py-1 text-sm font-medium rounded bg-[#e7f3ff] text-[#007bff]">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
                  </span>
                </td>
                <td class="px-4 py-3 font-medium text-[#1f2d3d]">{{ formatTime(slot.start_time) }}</td>
                <td class="px-4 py-3 font-medium text-[#1f2d3d]">{{ formatTime(slot.end_time) }}</td>
                <td class="px-4 py-3">
                  <span
                    v-if="slot.is_active"
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
                      :href="route('admin.slots.edit', slot.id)"
                      class="p-1.5 text-[#ffc107] hover:bg-[#ffc107] hover:text-[#1f2d3d] rounded transition"
                      :title="t('common.edit')"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button
                      @click="deleteSlot(slot.id)"
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-[#1f2d3d] mb-2">{{ t('common.noData') }}</h3>
          <p class="text-[#6c757d] mb-4">{{ t('slots.emptyMessage') }}</p>
          <button
            @click="generateDefaults"
            class="inline-flex items-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            {{ t('slots.generateDefaults') }}
          </button>
        </div>
      </div>

      <!-- Card Footer with Pagination -->
      <div v-if="slots.data.length > 0" class="px-4 py-3 bg-[#f8f9fa] border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-sm text-[#6c757d]">
            {{ t('common.total') }}: <strong>{{ slots.total }}</strong> {{ t('common.records') }}
          </div>
          <Pagination :links="slots.links" />
        </div>
      </div>
    </div>
  </div>
</template>
