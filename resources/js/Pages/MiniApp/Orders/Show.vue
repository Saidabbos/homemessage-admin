<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import MiniAppLayout from '@/Layouts/MiniAppLayout.vue';

defineOptions({ layout: MiniAppLayout });

const { t } = useI18n();

const props = defineProps({
  order: Object,
  payment: Object,
});

const showCancelModal = ref(false);
const cancelling = ref(false);

const statusColors = {
  NEW: 'bg-blue-500',
  CONFIRMING: 'bg-yellow-500',
  CONFIRMED: 'bg-green-500',
  WAITING_PAYMENT: 'bg-orange-500',
  PAID: 'bg-green-500',
  IN_PROGRESS: 'bg-purple-500',
  COMPLETED: 'bg-gray-500',
  CANCELLED: 'bg-red-500',
};

const statusBgColors = {
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

const canPay = () => {
  return props.order.can_be_paid && props.payment?.enabled;
};

const canCancel = () => {
  return ['NEW', 'CONFIRMING', 'CONFIRMED', 'WAITING_PAYMENT'].includes(props.order.status);
};

const canRate = () => {
  return props.order.status === 'COMPLETED' && !props.order.is_rated;
};

const handleRate = () => {
  router.post(`/app/orders/${props.order.id}/rate`);
};

const callMaster = () => {
  if (props.order.master?.phone) {
    window.location.href = `tel:${props.order.master.phone}`;
  }
};

const openMap = () => {
  if (props.order.address) {
    const query = encodeURIComponent(props.order.address);
    window.open(`https://maps.google.com/maps?q=${query}`, '_blank');
  }
};

const handlePay = (provider) => {
  router.post(`/api/miniapp/orders/${props.order.id}/pay`, { provider });
};

const handleCancel = () => {
  cancelling.value = true;
  router.post(`/app/orders/${props.order.id}/cancel`, {}, {
    onFinish: () => {
      cancelling.value = false;
      showCancelModal.value = false;
    }
  });
};
</script>

<template>
  <div class="min-h-screen bg-gray-50 pb-24">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div class="px-4 py-4">
        <div class="flex items-center gap-3">
          <Link href="/app/orders" class="text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </Link>
          <div class="flex-1">
            <h1 class="text-lg font-bold text-gray-900">{{ order.order_number }}</h1>
            <p class="text-sm text-gray-500">{{ order.created_at }}</p>
          </div>
          <div :class="['w-3 h-3 rounded-full', statusColors[order.status]]"></div>
        </div>
      </div>
    </div>

    <div class="p-4 space-y-4">
      <!-- Status Card -->
      <div class="bg-white rounded-2xl p-4 shadow-sm">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-500">{{ t('orders.status') }}</p>
            <span :class="['inline-block mt-1 px-3 py-1 text-sm font-medium rounded-full', statusBgColors[order.status]]">
              {{ t(`orderStatuses.${order.status}`) }}
            </span>
          </div>
          <span :class="['px-3 py-1 text-sm font-medium rounded-full', paymentColors[order.payment_status]]">
            {{ t(`paymentStatuses.${order.payment_status}`) }}
          </span>
        </div>
      </div>

      <!-- Date & Time -->
      <div class="bg-white rounded-2xl p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-3">{{ t('orders.dateTime') }}</h3>
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <p class="text-sm text-gray-500">{{ t('orders.date') }}</p>
            <p class="font-medium text-gray-900">{{ order.booking_date_display }}</p>
          </div>
          <div class="flex-1">
            <p class="text-sm text-gray-500">{{ t('orders.time') }}</p>
            <p class="font-medium text-gray-900">{{ order.arrival_window }}</p>
          </div>
        </div>
      </div>

      <!-- Service Details -->
      <div class="bg-white rounded-2xl p-4 shadow-sm">
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

      <!-- Master -->
      <div v-if="order.master" class="bg-white rounded-2xl p-4 shadow-sm">
        <h3 class="font-semibold text-gray-900 mb-3">{{ t('orders.master') }}</h3>
        <div class="flex items-center gap-4">
          <img
            v-if="order.master.photo_url"
            :src="order.master.photo_url"
            :alt="order.master.name"
            class="w-16 h-16 rounded-full object-cover"
          />
          <div v-else class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center">
            <svg class="w-8 h-8 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="font-semibold text-gray-900">{{ order.master.name }}</p>
            <p class="text-sm text-gray-500">{{ t('customer.yourMasseur') }}</p>
          </div>
          <button
            v-if="['CONFIRMED', 'IN_PROGRESS'].includes(order.status)"
            @click="callMaster"
            class="w-12 h-12 bg-green-500 text-white rounded-full flex items-center justify-center shadow-lg active:bg-green-600"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Address -->
      <div v-if="order.address" class="bg-white rounded-2xl p-4 shadow-sm">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900 mb-2">{{ t('orders.address') }}</h3>
            <p class="text-gray-900">{{ order.address }}</p>
            <p v-if="order.entrance || order.floor || order.apartment" class="text-sm text-gray-500 mt-1">
              <span v-if="order.entrance">{{ t('orders.entrance') }}: {{ order.entrance }}</span>
              <span v-if="order.floor"> • {{ t('orders.floor') }}: {{ order.floor }}</span>
              <span v-if="order.apartment"> • {{ t('orders.apartment') }}: {{ order.apartment }}</span>
            </p>
            <p v-if="order.landmark" class="text-sm text-gray-500 mt-1">
              {{ t('orders.landmark') }}: {{ order.landmark }}
            </p>
          </div>
          <button 
            @click="openMap"
            class="w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Timeline -->
      <div v-if="order.logs && order.logs.length > 0" class="bg-white rounded-2xl p-4 shadow-sm">
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
    </div>

    <!-- Bottom Actions -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 space-y-3">
      <!-- Pay Button -->
      <div v-if="canPay()" class="flex gap-3">
        <button
          v-if="payment.providers.includes('payme')"
          @click="handlePay('payme')"
          class="flex-1 py-3 bg-[#00CCCC] text-white font-semibold rounded-xl active:opacity-90"
        >
          Payme
        </button>
        <button
          v-if="payment.providers.includes('click')"
          @click="handlePay('click')"
          class="flex-1 py-3 bg-[#00A8E8] text-white font-semibold rounded-xl active:opacity-90"
        >
          Click
        </button>
      </div>

      <!-- Cancel Button -->
      <button
        v-if="canCancel()"
        @click="showCancelModal = true"
        class="w-full py-3 bg-red-50 text-red-600 font-semibold rounded-xl active:bg-red-100"
      >
        {{ t('orders.cancelOrder') }}
      </button>

      <!-- Rate Button -->
      <button
        v-if="canRate()"
        @click="handleRate"
        class="w-full py-3 bg-amber-500 text-white font-semibold rounded-xl active:bg-amber-600 flex items-center justify-center gap-2"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
        Masterni baholash
      </button>
    </div>

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black/50 flex items-end justify-center z-50">
      <div class="bg-white w-full rounded-t-3xl p-6 animate-slide-up">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ t('orders.cancelOrder') }}?</h3>
        <p class="text-gray-500 mb-6">Buyurtmani bekor qilmoqchimisiz?</p>
        <div class="flex gap-3">
          <button
            @click="showCancelModal = false"
            class="flex-1 py-3 bg-gray-100 text-gray-700 font-semibold rounded-xl"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            @click="handleCancel"
            :disabled="cancelling"
            class="flex-1 py-3 bg-red-600 text-white font-semibold rounded-xl disabled:opacity-50"
          >
            {{ cancelling ? '...' : t('orders.cancelOrder') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes slide-up {
  from { transform: translateY(100%); }
  to { transform: translateY(0); }
}
.animate-slide-up {
  animation: slide-up 0.3s ease-out;
}
</style>
