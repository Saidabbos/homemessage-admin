<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  order: Object,
  availableStatuses: Array,
  slots: Array,
  statusOptions: Array,
});

// Modals
const showStatusModal = ref(false);
const showSlotModal = ref(false);
const showNoteModal = ref(false);
const showCancelModal = ref(false);

// Forms
const statusForm = useForm({
  status: '',
  comment: '',
});

const slotForm = useForm({
  slot_id: props.order.slot_id,
  booking_date: props.order.booking_date?.split('T')[0] || '',
  comment: '',
});

const noteForm = useForm({
  note: '',
});

const cancelForm = useForm({
  reason: '',
});

// Actions
const openStatusModal = (status) => {
  statusForm.status = status;
  statusForm.comment = '';
  showStatusModal.value = true;
};

const submitStatus = () => {
  statusForm.post(route('admin.orders.update-status', props.order.id), {
    onSuccess: () => {
      showStatusModal.value = false;
      statusForm.reset();
    },
  });
};

const submitSlot = () => {
  slotForm.post(route('admin.orders.update-slot', props.order.id), {
    onSuccess: () => {
      showSlotModal.value = false;
    },
  });
};

const submitNote = () => {
  noteForm.post(route('admin.orders.add-note', props.order.id), {
    onSuccess: () => {
      showNoteModal.value = false;
      noteForm.reset();
    },
  });
};

const submitCancel = () => {
  cancelForm.post(route('admin.orders.cancel', props.order.id), {
    onSuccess: () => {
      showCancelModal.value = false;
      cancelForm.reset();
    },
  });
};

// Helpers
const getStatusClass = (status) => {
  const classes = {
    'NEW': 'bg-blue-100 text-blue-800 border-blue-200',
    'CONFIRMING': 'bg-yellow-100 text-yellow-800 border-yellow-200',
    'CONFIRMED': 'bg-green-100 text-green-800 border-green-200',
    'IN_PROGRESS': 'bg-purple-100 text-purple-800 border-purple-200',
    'COMPLETED': 'bg-gray-100 text-gray-800 border-gray-200',
    'CANCELLED': 'bg-red-100 text-red-800 border-red-200',
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

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('uz-UZ');
};

const formatTime = (time) => {
  if (!time) return '';
  return time.substring(0, 5);
};

const getStatusLabel = (status) => {
  return props.statusOptions?.find(s => s.value === status)?.label || status;
};
</script>

<template>
  <div>
    <!-- Content Header -->
    <div class="mb-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-[#1f2d3d]">{{ t('orders.orderDetails') }}</h1>
          <p class="text-sm text-[#6c757d] mt-1">{{ order.order_number }}</p>
        </div>
        <nav class="mt-2 sm:mt-0">
          <ol class="flex items-center text-sm">
            <li><Link href="/admin/dashboard" class="text-[#007bff]">{{ t('common.home') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li><Link :href="route('admin.orders.index')" class="text-[#007bff]">{{ t('orders.title') }}</Link></li>
            <li class="mx-2 text-[#6c757d]">/</li>
            <li class="text-[#6c757d]">{{ order.order_number }}</li>
          </ol>
        </nav>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <!-- Main Info -->
      <div class="lg:col-span-2 space-y-4">
        <!-- Order Status Card -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.status') }}</h3>
            <span
              class="px-3 py-1 text-sm font-medium rounded border"
              :class="getStatusClass(order.status)"
            >
              {{ getStatusLabel(order.status) }}
            </span>
          </div>
          <div class="p-4">
            <div class="flex flex-wrap gap-2">
              <button
                v-for="status in availableStatuses"
                :key="status"
                @click="openStatusModal(status)"
                class="px-4 py-2 text-sm font-medium rounded border transition"
                :class="{
                  'border-green-500 text-green-700 hover:bg-green-50': status === 'CONFIRMED' || status === 'COMPLETED',
                  'border-yellow-500 text-yellow-700 hover:bg-yellow-50': status === 'CONFIRMING' || status === 'IN_PROGRESS',
                  'border-red-500 text-red-700 hover:bg-red-50': status === 'CANCELLED',
                  'border-gray-300 text-gray-700 hover:bg-gray-50': !['CONFIRMED', 'COMPLETED', 'CONFIRMING', 'IN_PROGRESS', 'CANCELLED'].includes(status),
                }"
              >
                {{ getStatusLabel(status) }}
              </button>
              <button
                v-if="order.status !== 'CANCELLED' && order.status !== 'COMPLETED'"
                @click="showCancelModal = true"
                class="px-4 py-2 text-sm font-medium rounded border border-red-500 text-red-700 hover:bg-red-50 transition"
              >
                {{ t('orders.cancel') }}
              </button>
            </div>
          </div>
        </div>

        <!-- Booking Info -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.bookingInfo') }}</h3>
            <button
              v-if="order.status !== 'CANCELLED' && order.status !== 'COMPLETED'"
              @click="showSlotModal = true"
              class="text-sm text-[#007bff] hover:underline"
            >
              {{ t('orders.changeTime') }}
            </button>
          </div>
          <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.date') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ formatDate(order.booking_date) }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.time') }}</p>
                <p class="font-medium text-[#1f2d3d]">
                  {{ formatTime(order.slot?.start_time) }} - {{ formatTime(order.slot?.end_time) }}
                </p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.service') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.service_type?.name?.uz || order.service_type?.name }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.oil') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.oil?.name?.uz || order.oil?.name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.master') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.master?.first_name }} {{ order.master?.last_name }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.amount') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ Number(order.total_amount).toLocaleString() }} so'm</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.customerInfo') }}</h3>
          </div>
          <div class="p-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('customers.name') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.customer?.name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('customers.phone') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.customer?.phone || order.contact_phone || '-' }}</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-[#6c757d]">{{ t('orders.address') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.address || '-' }}</p>
                <p v-if="order.entrance || order.floor || order.apartment" class="text-sm text-[#6c757d]">
                  <span v-if="order.entrance">Pod: {{ order.entrance }}</span>
                  <span v-if="order.floor">, Qavat: {{ order.floor }}</span>
                  <span v-if="order.apartment">, Xonadon: {{ order.apartment }}</span>
                </p>
              </div>
              <div class="col-span-2" v-if="order.landmark">
                <p class="text-sm text-[#6c757d]">{{ t('orders.landmark') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.landmark }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Dispatcher Notes -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.dispatcherNotes') }}</h3>
            <button
              @click="showNoteModal = true"
              class="text-sm text-[#007bff] hover:underline"
            >
              {{ t('orders.addNote') }}
            </button>
          </div>
          <div class="p-4">
            <div v-if="order.dispatcher_notes" class="whitespace-pre-wrap text-sm text-[#1f2d3d]">
              {{ order.dispatcher_notes }}
            </div>
            <p v-else class="text-sm text-[#6c757d] italic">{{ t('orders.noNotes') }}</p>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-4">
        <!-- Payment Status -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.payment') }}</h3>
          </div>
          <div class="p-4">
            <span
              class="inline-flex px-3 py-1 text-sm font-medium rounded"
              :class="getPaymentStatusClass(order.payment_status)"
            >
              {{ order.payment_status === 'NOT_PAID' ? "To'lanmagan" : order.payment_status === 'PAID' ? "To'langan" : 'Qaytarilgan' }}
            </span>
            <p v-if="order.paid_at" class="mt-2 text-sm text-[#6c757d]">
              {{ t('orders.paidAt') }}: {{ formatDateTime(order.paid_at) }}
            </p>
          </div>
        </div>

        <!-- Order Timeline -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200">
            <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.history') }}</h3>
          </div>
          <div class="p-4">
            <div class="space-y-4">
              <div v-for="log in order.logs" :key="log.id" class="flex gap-3">
                <div class="w-2 h-2 mt-2 rounded-full bg-[#007bff] flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-[#1f2d3d]">
                    {{ log.action === 'status_changed' ? 'Status o\'zgartirildi' :
                       log.action === 'slot_changed' ? 'Vaqt o\'zgartirildi' :
                       log.action === 'note_added' ? 'Izoh qo\'shildi' :
                       log.action === 'created' ? 'Buyurtma yaratildi' :
                       log.action }}
                  </p>
                  <p v-if="log.old_value || log.new_value" class="text-xs text-[#6c757d]">
                    {{ log.old_value }} → {{ log.new_value }}
                  </p>
                  <p v-if="log.comment" class="text-xs text-[#6c757d] mt-1">{{ log.comment }}</p>
                  <p class="text-xs text-[#6c757d] mt-1">
                    {{ log.user?.name || 'Tizim' }} - {{ formatDateTime(log.created_at) }}
                  </p>
                </div>
              </div>
              <div class="flex gap-3">
                <div class="w-2 h-2 mt-2 rounded-full bg-green-500 flex-shrink-0"></div>
                <div>
                  <p class="text-sm font-medium text-[#1f2d3d]">{{ t('orders.created') }}</p>
                  <p class="text-xs text-[#6c757d]">{{ formatDateTime(order.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded shadow-sm p-4">
          <Link
            :href="route('admin.orders.index')"
            class="block w-full px-4 py-2 text-center text-sm font-medium text-[#6c757d] hover:text-[#1f2d3d] border border-gray-300 rounded hover:bg-gray-50 transition"
          >
            {{ t('common.back') }}
          </Link>
        </div>
      </div>
    </div>

    <!-- Status Change Modal -->
    <div v-if="showStatusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200">
          <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.changeStatus') }}</h3>
        </div>
        <div class="p-4">
          <p class="text-sm text-[#6c757d] mb-4">
            {{ t('orders.statusChangeConfirm') }}: <strong>{{ getStatusLabel(statusForm.status) }}</strong>
          </p>
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.comment') }}</label>
            <textarea
              v-model="statusForm.comment"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              :placeholder="t('orders.commentPlaceholder')"
            ></textarea>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="showStatusModal = false"
            class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            @click="submitStatus"
            :disabled="statusForm.processing"
            class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
          >
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Slot Change Modal -->
    <div v-if="showSlotModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200">
          <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.changeTime') }}</h3>
        </div>
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.date') }}</label>
            <input
              v-model="slotForm.booking_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.time') }}</label>
            <select
              v-model="slotForm.slot_id"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            >
              <option v-for="slot in slots" :key="slot.id" :value="slot.id">
                {{ formatTime(slot.start_time) }} - {{ formatTime(slot.end_time) }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.comment') }}</label>
            <textarea
              v-model="slotForm.comment"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            ></textarea>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="showSlotModal = false"
            class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            @click="submitSlot"
            :disabled="slotForm.processing"
            class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
          >
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Add Note Modal -->
    <div v-if="showNoteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200">
          <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.addNote') }}</h3>
        </div>
        <div class="p-4">
          <textarea
            v-model="noteForm.note"
            rows="4"
            class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            :placeholder="t('orders.notePlaceholder')"
          ></textarea>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="showNoteModal = false"
            class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            @click="submitNote"
            :disabled="noteForm.processing || !noteForm.note"
            class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50"
          >
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Cancel Modal -->
    <div v-if="showCancelModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200 bg-[#dc3545] rounded-t-lg">
          <h3 class="font-semibold text-white">{{ t('orders.cancelOrder') }}</h3>
        </div>
        <div class="p-4">
          <p class="text-sm text-[#6c757d] mb-4">{{ t('orders.cancelConfirm') }}</p>
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.cancelReason') }} *</label>
            <textarea
              v-model="cancelForm.reason"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              :placeholder="t('orders.cancelReasonPlaceholder')"
            ></textarea>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button
            @click="showCancelModal = false"
            class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition"
          >
            {{ t('common.cancel') }}
          </button>
          <button
            @click="submitCancel"
            :disabled="cancelForm.processing || !cancelForm.reason"
            class="px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition disabled:opacity-50"
          >
            {{ t('orders.confirmCancel') }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
