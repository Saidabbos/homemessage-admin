<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/UI/Pagination.vue';

// Simple debounce function
const debounce = (fn, delay) => {
  let timeoutId;
  return (...args) => {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn(...args), delay);
  };
};

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  orders: Object,
  filters: Object,
  masters: Array,
  statuses: Array,
  paymentStatuses: Array,
  statusCounts: Object,
  isNewOrdersPage: {
    type: Boolean,
    default: false,
  },
});

const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const paymentStatusFilter = ref(props.filters?.payment_status || '');
const masterFilter = ref(props.filters?.master_id || '');

const applyFilters = debounce(() => {
  router.get(
    props.isNewOrdersPage ? route('admin.orders.new') : route('admin.orders.index'),
    {
      search: search.value || undefined,
      status: statusFilter.value || undefined,
      payment_status: paymentStatusFilter.value || undefined,
      master_id: masterFilter.value || undefined,
    },
    { preserveState: true, replace: true }
  );
}, 300);

watch([search, statusFilter, paymentStatusFilter, masterFilter], applyFilters);

const resetFilters = () => {
  search.value = '';
  statusFilter.value = '';
  paymentStatusFilter.value = '';
  masterFilter.value = '';
};

const getStatusClass = (status) => {
  const classes = {
    'NEW': 'bg-blue-100 text-blue-800',
    'CONFIRMING': 'bg-yellow-100 text-yellow-800',
    'CONFIRMED': 'bg-green-100 text-green-800',
    'IN_PROGRESS': 'bg-purple-100 text-purple-800',
    'COMPLETED': 'bg-gray-100 text-gray-800',
    'CANCELLED': 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-600';
};

const getPaymentStatusClass = (status) => {
  const classes = {
    'NOT_PAID': 'bg-red-100 text-red-800',
    'PAID': 'bg-green-100 text-green-800',
    'REFUNDED': 'bg-yellow-100 text-yellow-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-600';
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ');
};

const formatTime = (slot) => {
  if (!slot) return '-';
  return `${slot.start_time?.substring(0, 5)} - ${slot.end_time?.substring(0, 5)}`;
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">
            {{ isNewOrdersPage ? t('orders.newOrders') : t('orders.title') }}
          </h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ t('orders.subtitle') }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ t('orders.title') }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-4">
      <div
        v-for="status in statuses"
        :key="status.value"
        class="bg-white rounded shadow-sm p-4 cursor-pointer hover:shadow-md transition"
        :class="{ 'ring-2 ring-[#007bff]': statusFilter === status.value }"
        @click="statusFilter = statusFilter === status.value ? '' : status.value"
      >
        <div class="text-2xl font-bold text-[#1f2d3d]">{{ statusCounts[status.value] || 0 }}</div>
        <div class="text-sm text-[#6c757d]">{{ status.label }}</div>
      </div>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded shadow-sm">
      <!-- Filters -->
      <div class="px-4 py-3 bg-[#f8f9fa] border-b border-gray-200">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
          <input
            v-model="search"
            type="text"
            :placeholder="t('orders.searchPlaceholder')"
            class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
          />
          <select
            v-if="!isNewOrdersPage"
            v-model="statusFilter"
            class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
          >
            <option value="">{{ t('orders.allStatuses') }}</option>
            <option v-for="status in statuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
          <select
            v-model="paymentStatusFilter"
            class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
          >
            <option value="">{{ t('orders.allPaymentStatuses') }}</option>
            <option v-for="status in paymentStatuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
          <select
            v-model="masterFilter"
            class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
          >
            <option value="">{{ t('orders.allMasters') }}</option>
            <option v-for="master in masters" :key="master.id" :value="master.id">
              {{ master.first_name }} {{ master.last_name }}
            </option>
          </select>
          <button
            @click="resetFilters"
            class="px-4 py-2 text-sm text-[#6c757d] hover:text-[#1f2d3d] border border-gray-300 rounded hover:bg-gray-50 transition"
          >
            {{ t('common.reset') }}
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-[#f8f9fa] border-b border-gray-200">
            <tr>
              <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('orders.orderNumber') }}</th>
              <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('orders.customer') }}</th>
              <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('orders.master') }}</th>
              <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('orders.dateTime') }}</th>
              <th class="px-4 py-3 text-left font-semibold text-[#6c757d]">{{ t('orders.service') }}</th>
              <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">{{ t('common.status') }}</th>
              <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">{{ t('orders.payment') }}</th>
              <th class="px-4 py-3 text-center font-semibold text-[#6c757d]">{{ t('common.actions') }}</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-[#f8f9fa]">
              <td class="px-4 py-3 font-medium text-[#007bff]">
                <Link :href="route('admin.orders.show', order.id)">
                  {{ order.order_number }}
                </Link>
              </td>
              <td class="px-4 py-3">
                <div class="font-medium text-[#1f2d3d]">{{ order.customer?.name || '-' }}</div>
                <div class="text-xs text-[#6c757d]">{{ order.customer?.phone || order.contact_phone }}</div>
              </td>
              <td class="px-4 py-3">
                {{ order.master?.first_name }} {{ order.master?.last_name }}
              </td>
              <td class="px-4 py-3">
                <div class="font-medium">{{ formatDate(order.booking_date) }}</div>
                <div class="text-xs text-[#6c757d]">{{ formatTime(order.slot) }}</div>
              </td>
              <td class="px-4 py-3">
                {{ order.service_type?.name?.uz || order.service_type?.name }}
              </td>
              <td class="px-4 py-3 text-center">
                <span
                  class="inline-flex px-2 py-1 text-xs font-medium rounded"
                  :class="getStatusClass(order.status)"
                >
                  {{ statuses.find(s => s.value === order.status)?.label || order.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span
                  class="inline-flex px-2 py-1 text-xs font-medium rounded"
                  :class="getPaymentStatusClass(order.payment_status)"
                >
                  {{ paymentStatuses.find(s => s.value === order.payment_status)?.label || order.payment_status }}
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <Link
                  :href="route('admin.orders.show', order.id)"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-[#007bff] hover:bg-[#e7f3ff] rounded transition"
                >
                  {{ t('common.view') }}
                </Link>
              </td>
            </tr>
            <tr v-if="orders.data.length === 0">
              <td colspan="8" class="px-4 py-12 text-center text-[#6c757d]">
                {{ t('orders.emptyMessage') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="orders.data.length > 0" class="px-4 py-3 bg-[#f8f9fa] border-t border-gray-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <p class="text-sm text-[#6c757d]">
            {{ t('common.total') }}: {{ orders.total }} {{ t('common.records') }}
          </p>
          <Pagination :links="orders.links" />
        </div>
      </div>
    </div>
  </div>
</template>
