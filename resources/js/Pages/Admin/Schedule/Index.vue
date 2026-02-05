<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  masters: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');

const applyFilters = () => {
  router.get(route('admin.schedule.index'), {
    search: search.value || undefined,
  }, {
    preserveState: true,
    replace: true,
  });
};

const resetFilters = () => {
  search.value = '';
  router.get(route('admin.schedule.index'));
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});

const hasActiveFilters = () => search.value;
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('schedule.title') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('schedule.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('schedule.title') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow-sm">
      <!-- Card Header -->
      <div class="px-4 py-3 border-b border-gray-200">
        <h3 class="font-semibold text-[#1f2d3d] flex items-center">
          <svg class="w-5 h-5 mr-2 text-[#17a2b8]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          {{ t('schedule.selectMaster') }}
        </h3>
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
      <div class="p-4">
        <!-- Masters Grid -->
        <div v-if="masters.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <Link
            v-for="master in masters.data"
            :key="master.id"
            :href="route('admin.schedule.show', master.id)"
            class="block p-4 border border-gray-200 rounded-lg hover:border-[#007bff] hover:shadow-md transition"
          >
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div v-if="master.photo" class="w-12 h-12 rounded-full overflow-hidden">
                  <img :src="'/storage/' + master.photo" :alt="master.first_name" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-12 h-12 rounded-full bg-[#007bff] flex items-center justify-center text-white font-semibold">
                  {{ master.first_name?.charAt(0) }}{{ master.last_name?.charAt(0) }}
                </div>
              </div>
              <div class="ml-4 flex-1">
                <h4 class="font-semibold text-[#1f2d3d]">{{ master.first_name }} {{ master.last_name }}</h4>
                <p class="text-sm text-[#6c757d]">{{ master.phone }}</p>
              </div>
              <svg class="w-5 h-5 text-[#6c757d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </div>
          </Link>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
          <div class="w-16 h-16 bg-[#e9ecef] rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-[#6c757d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-[#1f2d3d] mb-2">{{ t('schedule.noMasters') }}</h3>
          <p class="text-[#6c757d] mb-4">{{ t('schedule.noMastersMessage') }}</p>
          <Link
            href="/admin/masters/create"
            class="inline-flex items-center px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition"
          >
            {{ t('masters.new') }}
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
