<script setup>
import { ref, computed, watch } from 'vue';
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

const formatArrivalWindow = (order) => {
  if (!order.arrival_window_start || !order.arrival_window_end) return '-';
  const start = order.arrival_window_start?.substring(0, 5);
  const end = order.arrival_window_end?.substring(0, 5);
  return `${start} – ${end}`;
};

// Tree table: group orders by booking_group_id
const groupColors = ['#007bff', '#28a745', '#fd7e14', '#6f42c1', '#e83e8c', '#17a2b8'];
const expandedGroups = ref({});

const treeRows = computed(() => {
  const rows = [];
  const grouped = {};
  const singles = [];
  let colorIdx = 0;

  // Separate grouped vs single orders
  props.orders.data.forEach(order => {
    if (order.booking_group_id && order.group_count > 1) {
      if (!grouped[order.booking_group_id]) {
        grouped[order.booking_group_id] = {
          color: groupColors[colorIdx % groupColors.length],
          orders: [],
        };
        colorIdx++;
      }
      grouped[order.booking_group_id].orders.push(order);
    } else {
      singles.push({ type: 'single', order });
    }
  });

  // Build tree rows preserving original order
  const seen = {};
  props.orders.data.forEach(order => {
    if (order.booking_group_id && order.group_count > 1) {
      if (!seen[order.booking_group_id]) {
        seen[order.booking_group_id] = true;
        const group = grouped[order.booking_group_id];
        // Parent row (first order in group)
        rows.push({
          type: 'parent',
          order: group.orders[0],
          groupId: order.booking_group_id,
          groupColor: group.color,
          childCount: group.orders.length,
        });
        // Child rows (remaining orders)
        for (let i = 1; i < group.orders.length; i++) {
          rows.push({
            type: 'child',
            order: group.orders[i],
            groupId: order.booking_group_id,
            groupColor: group.color,
          });
        }
      }
    } else {
      rows.push({ type: 'single', order });
    }
  });

  return rows;
});

const toggleGroup = (groupId) => {
  expandedGroups.value[groupId] = !isExpanded(groupId);
};

const isExpanded = (groupId) => {
  return expandedGroups.value[groupId] !== false; // default expanded
};

const isRowVisible = (row) => {
  if (row.type !== 'child') return true;
  return isExpanded(row.groupId);
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
        <div class="text-sm text-[#6c757d]">{{ t(`orderStatuses.${status.value}`) }}</div>
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
              {{ t(`orderStatuses.${status.value}`) }}
            </option>
          </select>
          <select
            v-model="paymentStatusFilter"
            class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
          >
            <option value="">{{ t('orders.allPaymentStatuses') }}</option>
            <option v-for="status in paymentStatuses" :key="status.value" :value="status.value">
              {{ t(`paymentStatuses.${status.value}`) }}
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
            <template v-for="row in treeRows" :key="row.order.id">
              <tr
                v-if="isRowVisible(row)"
                class="hover:bg-[#f8f9fa] transition-colors"
                :style="row.type !== 'single' ? { borderLeft: `4px solid ${row.groupColor}` } : {}"
                :class="{
                  'bg-[#f0f4ff]': row.type === 'parent',
                  'bg-[#fafbff]': row.type === 'child',
                }"
              >
                <!-- Order Number -->
                <td class="px-4 py-3 font-medium text-[#007bff]">
                  <div class="flex items-center gap-2">
                    <!-- Tree toggle for parent rows -->
                    <button
                      v-if="row.type === 'parent'"
                      @click="toggleGroup(row.groupId)"
                      class="flex items-center justify-center w-5 h-5 rounded hover:bg-gray-200 transition-colors"
                    >
                      <svg
                        class="w-4 h-4 text-[#6c757d] transition-transform"
                        :class="{ 'rotate-90': isExpanded(row.groupId) }"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                      </svg>
                    </button>
                    <!-- Tree indent for child rows -->
                    <span v-if="row.type === 'child'" class="flex items-center w-5 pl-1">
                      <svg class="w-3.5 h-3.5 text-gray-300" viewBox="0 0 16 16" fill="none">
                        <path d="M4 0v10h12" stroke="currentColor" stroke-width="1.5" fill="none" />
                      </svg>
                    </span>
                    <Link :href="route('admin.orders.show', row.order.id)" class="flex items-center gap-2">
                      {{ row.order.order_number }}
                      <span
                        v-if="row.type === 'parent'"
                        class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded"
                        :style="{ backgroundColor: row.groupColor + '18', color: row.groupColor }"
                      >
                        <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ row.childCount }}
                      </span>
                    </Link>
                  </div>
                </td>
                <!-- Customer -->
                <td class="px-4 py-3">
                  <div class="font-medium text-[#1f2d3d]">{{ row.order.customer?.name || '-' }}</div>
                  <div class="text-xs text-[#6c757d]">{{ row.order.customer?.phone || row.order.contact_phone }}</div>
                </td>
                <!-- Master -->
                <td class="px-4 py-3">
                  {{ row.order.master?.first_name }} {{ row.order.master?.last_name }}
                </td>
                <!-- Date/Time -->
                <td class="px-4 py-3">
                  <div class="font-medium">{{ formatDate(row.order.booking_date) }}</div>
                  <div class="text-xs text-[#6c757d]">{{ formatArrivalWindow(row.order) }}</div>
                </td>
                <!-- Service -->
                <td class="px-4 py-3">
                  {{ row.order.service_type?.name?.uz || row.order.service_type?.name }}
                </td>
                <!-- Status -->
                <td class="px-4 py-3 text-center">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-medium rounded"
                    :class="getStatusClass(row.order.status)"
                  >
                    {{ t(`orderStatuses.${row.order.status}`) }}
                  </span>
                </td>
                <!-- Payment -->
                <td class="px-4 py-3 text-center">
                  <span
                    class="inline-flex px-2 py-1 text-xs font-medium rounded"
                    :class="getPaymentStatusClass(row.order.payment_status)"
                  >
                    {{ t(`paymentStatuses.${row.order.payment_status}`) }}
                  </span>
                </td>
                <!-- Actions -->
                <td class="px-4 py-3 text-center">
                  <Link
                    :href="route('admin.orders.show', row.order.id)"
                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-[#007bff] hover:bg-[#e7f3ff] rounded transition"
                  >
                    {{ t('common.view') }}
                  </Link>
                </td>
              </tr>
            </template>
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
