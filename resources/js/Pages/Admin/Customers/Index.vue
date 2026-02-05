<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  customers: Object,
  filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const applyFilters = () => {
  router.get(route('admin.customers.index'), {
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
  router.get(route('admin.customers.index'));
};

let searchTimeout;
watch(search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(applyFilters, 300);
});

watch(status, applyFilters);

const deleteCustomer = (id) => {
  if (confirm(t('customers.confirmDelete'))) {
    router.delete(route('admin.customers.destroy', id));
  }
};

const hasActiveFilters = () => search.value || status.value;

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('customers.title') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('customers.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('customers.title') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow-sm">
      <!-- Card Header -->
      <div class="px-4 py-3 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h3 class="font-semibold text-[#1f2d3d] flex items-center">
          <svg class="w-5 h-5 mr-2 text-[#20c997]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
          </svg>
          {{ t('customers.list') }}
        </h3>
        <div class="text-sm text-[#6c757d]">
          {{ t('customers.registeredViaApp') }}
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
        <div v-if="customers.data.length > 0" class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-[#f8f9fa] border-b border-gray-200">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">#</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('customers.name') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('customers.email') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('customers.phone') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('common.createdAt') }}</th>
                <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('common.status') }}</th>
                <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">{{ t('common.actions') }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr v-for="(customer, index) in customers.data" :key="customer.id" class="hover:bg-[#f8f9fa] transition">
                <td class="px-4 py-3 text-[#6c757d]">{{ index + 1 }}</td>
                <td class="px-4 py-3">
                  <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full bg-[#20c997] flex items-center justify-center text-white text-sm font-semibold mr-3">
                      {{ customer.name.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-medium text-[#1f2d3d]">{{ customer.name }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-[#6c757d]">{{ customer.email }}</td>
                <td class="px-4 py-3 text-[#6c757d]">{{ customer.phone || '-' }}</td>
                <td class="px-4 py-3 text-[#6c757d]">{{ formatDate(customer.created_at) }}</td>
                <td class="px-4 py-3">
                  <span
                    v-if="customer.status"
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
                      :href="route('admin.customers.show', customer.id)"
                      class="p-1.5 text-[#17a2b8] hover:bg-[#17a2b8] hover:text-white rounded transition"
                      :title="t('common.view')"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                      </svg>
                    </Link>
                    <Link
                      :href="route('admin.customers.edit', customer.id)"
                      class="p-1.5 text-[#ffc107] hover:bg-[#ffc107] hover:text-[#1f2d3d] rounded transition"
                      :title="t('common.edit')"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                      </svg>
                    </Link>
                    <button
                      @click="deleteCustomer(customer.id)"
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
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-[#1f2d3d] mb-2">{{ t('common.noData') }}</h3>
          <p class="text-[#6c757d]">{{ t('customers.emptyMessage') }}</p>
        </div>
      </div>

      <!-- Card Footer with Pagination -->
      <div v-if="customers.data.length > 0" class="px-4 py-3 bg-[#f8f9fa] border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div class="text-sm text-[#6c757d]">
            {{ t('common.total') }}: <strong>{{ customers.total }}</strong> {{ t('common.records') }}
          </div>
          <Pagination :links="customers.links" />
        </div>
      </div>
    </div>
  </div>
</template>
