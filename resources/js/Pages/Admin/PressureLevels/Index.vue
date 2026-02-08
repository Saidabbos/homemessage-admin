<script setup>
import { ref, watch } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
  pressureLevels: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const applyFilters = () => {
  router.get(route('admin.pressure-levels.index'), {
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
  router.get(route('admin.pressure-levels.index'));
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});

watch(status, applyFilters);

const getTranslation = (item, field) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field].uz || item[field].en || Object.values(item[field])[0] || '';
};

const deletePressureLevel = (id) => {
  if (confirm('Haqiqatan ham bu bosim darajasini o\'chirmoqchimisiz?')) {
    router.delete(route('admin.pressure-levels.destroy', id));
  }
};

const hasActiveFilters = () => search.value || status.value;
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">Bosim Darajasi</h1>
          <p class="text-sm text-[#6c757d] mt-1">Masaj bosim darajalarini boshqarish</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">Bosh sahifa</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">Bosim Darajasi</li>
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
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 0V5a2 2 0 012-2h2a2 2 0 012 2v2m0 0h2"/>
          </svg>
          Bosim Darajalari
        </h3>
        <Link
          href="/admin/pressure-levels/create"
          class="inline-flex items-center px-4 py-2 bg-[#28a745] text-white text-sm font-medium rounded hover:bg-[#218838] transition"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Qo'shish
        </Link>
      </div>

      <!-- Filters -->
      <div class="px-4 py-3 bg-[#f8f9fa] border-b border-gray-200">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="flex-1">
            <input
              type="text"
              v-model="search"
              placeholder="Qidirish..."
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            />
          </div>
          <div class="sm:w-40">
            <select
              v-model="status"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            >
              <option value="">Barcha holatlar</option>
              <option value="active">Faol</option>
              <option value="inactive">Nofaol</option>
            </select>
          </div>
          <button
            v-if="hasActiveFilters()"
            @click="resetFilters"
            class="px-3 py-2 text-sm text-[#6c757d] hover:text-[#1f2d3d] transition"
          >
            Tiklash
          </button>
        </div>
      </div>

      <!-- Card Body -->
      <div class="p-0">
        <!-- Table -->
        <div v-if="pressureLevels.data.length > 0" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-[#f8f9fa] border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">#</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">Nomi</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">Slug</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">Tartib</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">Holat</th>
                <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">Amallar</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(level, index) in pressureLevels.data" :key="level.id" class="hover:bg-[#f8f9fa] transition">
                <td class="px-4 py-3 text-[#6c757d]">{{ index + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="font-medium text-[#1f2d3d]">{{ getTranslation(level, 'name') }}</div>
                </td>
                <td class="px-4 py-3">
                  <code class="bg-[#f8f9fa] px-2 py-1 rounded text-[#1f2d3d] text-xs">{{ level.slug }}</code>
                </td>
                <td class="px-4 py-3">
                  <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#e7f3ff] text-[#0c5aa0]">
                    {{ level.sort_order }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <span
                    v-if="level.status"
                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#d4edda] text-[#155724]"
                  >
                    <span class="w-1.5 h-1.5 bg-[#28a745] rounded-full mr-1.5"></span>
                    Faol
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded bg-[#f8d7da] text-[#721c24]"
                  >
                    <span class="w-1.5 h-1.5 bg-[#dc3545] rounded-full mr-1.5"></span>
                    Nofaol
                  </span>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center justify-center gap-1">
                    <Link
                      :href="route('admin.pressure-levels.show', level.id)"
                      class="p-1.5 text-[#17a2b8] hover:bg-[#17a2b8] hover:text-white rounded transition"
                      :title="'Ko\'rish'"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <Link
                      :href="route('admin.pressure-levels.edit', level.id)"
                      class="p-1.5 text-[#ffc107] hover:bg-[#ffc107] hover:text-[#1f2d3d] rounded transition"
                      :title="'Tahrirlash'"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button
                      @click="deletePressureLevel(level.id)"
                      class="p-1.5 text-[#dc3545] hover:bg-[#dc3545] hover:text-white rounded transition"
                      :title="'O\'chirish'"
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 0V5a2 2 0 012-2h2a2 2 0 012 2v2m0 0h2"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-[#1f2d3d] mb-2">Ma'lumot yo'q</h3>
          <p class="text-[#6c757d] mb-4">Bosim darajalari topilmadi</p>
          <Link
            href="/admin/pressure-levels/create"
            class="inline-flex items-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Birinchi qo'shish
          </Link>
        </div>
      </div>

      <!-- Card Footer with Pagination -->
      <div v-if="pressureLevels.data.length > 0" class="px-4 py-3 bg-[#f8f9fa] border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-sm text-[#6c757d]">
            Jami: <strong>{{ pressureLevels.total }}</strong> ta
          </div>
          <Pagination :links="pressureLevels.links" />
        </div>
      </div>
    </div>
  </div>
</template>
