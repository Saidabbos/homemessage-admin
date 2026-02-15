<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const { t } = useI18n();

const props = defineProps({
  orders: Object,
});

const statusColors = {
  NEW: 'bg-blue-100 text-blue-800',
  CONFIRMING: 'bg-yellow-100 text-yellow-800',
  CONFIRMED: 'bg-green-100 text-green-800',
  WAITING_PAYMENT: 'bg-orange-100 text-orange-800',
  PAID: 'bg-green-100 text-green-800',
  IN_PROGRESS: 'bg-purple-100 text-purple-800',
  COMPLETED: 'bg-gray-100 text-gray-800',
  CANCELLED: 'bg-red-100 text-red-800',
};

const paymentColors = {
  NOT_PAID: 'bg-red-100 text-red-800',
  PENDING: 'bg-yellow-100 text-yellow-800',
  INVOICED: 'bg-orange-100 text-orange-800',
  WAITING_PAYMENT: 'bg-orange-100 text-orange-800',
  PAID: 'bg-green-100 text-green-800',
  FAILED: 'bg-red-100 text-red-800',
  REFUNDED: 'bg-gray-100 text-gray-800',
  CANCELLED: 'bg-gray-100 text-gray-800',
};

const formatPrice = (amount) => {
  return new Intl.NumberFormat('uz-UZ').format(amount) + " so'm";
};
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-4">
        <div class="flex items-center gap-3">
          <Link href="/app" class="text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>
          <h1 class="text-xl font-bold text-gray-900">{{ t('customer.myOrders') }}</h1>
        </div>
      </div>
    </div>

    <!-- Orders List -->
    <div class="p-4">
      <div v-if="orders.data.length > 0" class="space-y-3">
        <Link
          v-for="order in orders.data"
          :key="order.id"
          :href="`/app/orders/${order.id}`"
          class="block bg-white rounded-2xl shadow-sm p-4 active:bg-gray-50 transition-colors"
        >
          <!-- Header -->
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="font-semibold text-gray-900">{{ order.order_number }}</p>
              <p class="text-sm text-gray-500">{{ order.booking_date_display }} • {{ order.arrival_window }}</p>
            </div>
            <span :class="['px-2 py-1 text-xs font-medium rounded-full', statusColors[order.status]]">
              {{ t(`orderStatuses.${order.status}`) }}
            </span>
          </div>

          <!-- Master & Service -->
          <div class="flex items-center gap-3 mb-3">
            <img
              v-if="order.master?.photo_url"
              :src="order.master.photo_url"
              :alt="order.master.name"
              class="w-12 h-12 rounded-full object-cover"
            />
            <div v-else class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center">
              <svg class="w-6 h-6 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-gray-900 truncate">{{ order.master?.name || t('customer.masterNotAssigned') }}</p>
              <p class="text-sm text-gray-500">{{ order.service_type?.name }} • {{ order.duration?.minutes }} {{ t('common.min') }}</p>
            </div>
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <span :class="['px-2 py-1 text-xs font-medium rounded-full', paymentColors[order.payment_status]]">
              {{ t(`paymentStatuses.${order.payment_status}`) }}
            </span>
            <span class="font-bold text-gray-900">{{ formatPrice(order.total_amount) }}</span>
          </div>
        </Link>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-16">
        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ t('customer.noOrders') }}</h3>
        <p class="text-gray-500 mb-6">{{ t('customer.bookFirst') }}</p>
        <Link 
          href="/app/book" 
          class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 text-white font-semibold rounded-full hover:bg-purple-700 transition-colors"
        >
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          {{ t('customer.newBooking') }}
        </Link>
      </div>

      <!-- Pagination -->
      <div v-if="orders.last_page > 1" class="flex justify-center gap-2 pt-6">
        <Link
          v-for="page in orders.last_page"
          :key="page"
          :href="`/app/orders?page=${page}`"
          class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-medium transition-colors"
          :class="page === orders.current_page 
            ? 'bg-purple-600 text-white' 
            : 'bg-white text-gray-600 hover:bg-gray-100'"
        >
          {{ page }}
        </Link>
      </div>
    </div>
  </div>
</template>
