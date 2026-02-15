<script setup>
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

defineOptions({ layout: CustomerLayout });

const { t } = useI18n();

const props = defineProps({
  order: Object,
});

const statusColors = {
  NEW: 'bg-blue-500',
  CONFIRMING: 'bg-yellow-500',
  CONFIRMED: 'bg-green-500',
  IN_PROGRESS: 'bg-purple-500',
  COMPLETED: 'bg-gray-500',
  CANCELLED: 'bg-red-500',
};

const paymentColors = {
  NOT_PAID: 'bg-red-100 text-red-800',
  PENDING: 'bg-yellow-100 text-yellow-800',
  PAID: 'bg-green-100 text-green-800',
  FAILED: 'bg-red-100 text-red-800',
  REFUNDED: 'bg-gray-100 text-gray-800',
  CANCELLED: 'bg-gray-100 text-gray-800',
};

const formatPrice = (amount) => {
  return new Intl.NumberFormat('uz-UZ').format(amount) + ' so\'m';
};

const callMaster = () => {
  if (props.order.master?.phone) {
    window.location.href = `tel:${props.order.master.phone}`;
  }
};
</script>

<template>
  <div class="max-w-2xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
      <Link href="/customer/orders" class="text-purple-600 hover:text-purple-800 flex items-center gap-1 mb-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        {{ t('customer.myOrders') }}
      </Link>
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">{{ order.order_number }}</h1>
        <div :class="['w-3 h-3 rounded-full', statusColors[order.status]]"></div>
      </div>
    </div>

    <!-- Status Card -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm text-gray-500">{{ t('orders.status') }}</p>
          <p class="font-semibold text-gray-900">{{ t(`orderStatuses.${order.status}`) }}</p>
        </div>
        <span :class="['px-3 py-1 text-sm font-medium rounded-full', paymentColors[order.payment_status]]">
          {{ t(`paymentStatuses.${order.payment_status}`) }}
        </span>
      </div>
    </div>

    <!-- Date & Time Card -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <h3 class="font-semibold text-gray-900 mb-3">{{ t('orders.dateTime') }}</h3>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-gray-500">{{ t('orders.date') }}</p>
          <p class="font-medium text-gray-900">{{ order.booking_date_display }}</p>
        </div>
        <div>
          <p class="text-sm text-gray-500">{{ t('orders.time') }}</p>
          <p class="font-medium text-gray-900">{{ order.arrival_window }}</p>
        </div>
      </div>
    </div>

    <!-- Service Card -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <h3 class="font-semibold text-gray-900 mb-3">{{ t('orders.service') }}</h3>
      <div class="space-y-3">
        <div class="flex justify-between">
          <span class="text-gray-500">{{ t('orders.massageType') }}</span>
          <span class="font-medium text-gray-900">{{ order.service_type?.name || '-' }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">{{ t('orders.duration') }}</span>
          <span class="font-medium text-gray-900">{{ order.duration?.minutes }} {{ t('common.min') }}</span>
        </div>
        <div v-if="order.oil" class="flex justify-between">
          <span class="text-gray-500">{{ t('orders.oil') }}</span>
          <span class="font-medium text-gray-900">{{ order.oil.name }}</span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">{{ t('orders.peopleCount') }}</span>
          <span class="font-medium text-gray-900">{{ order.people_count }}</span>
        </div>
        <div v-if="order.pressure_level" class="flex justify-between">
          <span class="text-gray-500">{{ t('orders.pressure') }}</span>
          <span class="font-medium text-gray-900">{{ t(`pressureLevels.${order.pressure_level}`) }}</span>
        </div>
        <div class="flex justify-between pt-3 border-t border-gray-100">
          <span class="font-semibold text-gray-900">{{ t('orders.total') }}</span>
          <span class="font-bold text-purple-600 text-lg">{{ formatPrice(order.total_amount) }}</span>
        </div>
      </div>
    </div>

    <!-- Master Card -->
    <div v-if="order.master" class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <h3 class="font-semibold text-gray-900 mb-3">{{ t('orders.master') }}</h3>
      <div class="flex items-center gap-4">
        <img
          :src="order.master.photo_url"
          :alt="order.master.name"
          class="w-16 h-16 rounded-full object-cover"
        />
        <div class="flex-1">
          <p class="font-semibold text-gray-900">{{ order.master.name }}</p>
          <p class="text-sm text-gray-500">{{ t('customer.yourMasseur') }}</p>
        </div>
        <button
          v-if="order.status === 'CONFIRMED' || order.status === 'IN_PROGRESS'"
          @click="callMaster"
          class="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Address Card -->
    <div v-if="order.address" class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <h3 class="font-semibold text-gray-900 mb-3">{{ t('orders.address') }}</h3>
      <div class="space-y-2">
        <p class="text-gray-900">{{ order.address }}</p>
        <div v-if="order.entrance || order.floor || order.apartment" class="text-sm text-gray-500">
          <span v-if="order.entrance">{{ t('orders.entrance') }}: {{ order.entrance }}</span>
          <span v-if="order.floor"> • {{ t('orders.floor') }}: {{ order.floor }}</span>
          <span v-if="order.apartment"> • {{ t('orders.apartment') }}: {{ order.apartment }}</span>
        </div>
        <p v-if="order.landmark" class="text-sm text-gray-500">
          {{ t('orders.landmark') }}: {{ order.landmark }}
        </p>
      </div>
    </div>

    <!-- Order Timeline -->
    <div v-if="order.logs && order.logs.length > 0" class="bg-white rounded-xl shadow-sm p-4 mb-4">
      <h3 class="font-semibold text-gray-900 mb-3">{{ t('customer.orderTimeline') }}</h3>
      <div class="space-y-3">
        <div v-for="log in order.logs" :key="log.id" class="flex items-start gap-3">
          <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
          <div>
            <p class="font-medium text-gray-900">{{ t(`orderActions.${log.action}`, log.action) }}</p>
            <p class="text-sm text-gray-500">{{ log.created_at }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Order Info -->
    <div class="text-center text-sm text-gray-500 mt-6">
      <p>{{ t('customer.orderCreated') }}: {{ order.created_at }}</p>
    </div>
  </div>
</template>
