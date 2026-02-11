<script setup>
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps({
  order: Object,
  availableStatuses: Array,
  statusOptions: Array,
  callOutcomeOptions: Array,
  paymentProviders: Object,
});

// Modals
const showStatusModal = ref(false);
const showRescheduleModal = ref(false);
const showNoteModal = ref(false);
const showCancelModal = ref(false);
const showConfirmationModal = ref(false);
const showQaModal = ref(false);

// Work Order
const sendingWorkOrder = ref(false);

// Payment link generation
const generatingPaymentLink = ref(false);
const generatedPaymentLink = ref('');
const paymentLinkProvider = ref('');

// Forms
const statusForm = useForm({
  status: '',
  comment: '',
});

const rescheduleForm = useForm({
  booking_date: props.order.booking_date?.split('T')[0] || '',
  arrival_window_start: props.order.arrival_window_start?.substring(0, 5) || '',
  arrival_window_end: props.order.arrival_window_end?.substring(0, 5) || '',
  comment: '',
});

const noteForm = useForm({
  note: '',
});

const cancelForm = useForm({
  reason: '',
});

const confirmationForm = useForm({
  call_outcome: props.order.call_outcome || 'pending',
  conf_entrance: props.order.conf_entrance || props.order.entrance || '',
  conf_floor: props.order.conf_floor || props.order.floor || '',
  conf_elevator: props.order.conf_elevator || false,
  conf_parking: props.order.conf_parking || '',
  conf_landmark: props.order.conf_landmark || props.order.landmark || '',
  conf_onsite_phone: props.order.conf_onsite_phone || props.order.contact_phone || '',
  conf_constraints: props.order.conf_constraints || '',
  conf_space_ok: props.order.conf_space_ok || false,
  conf_pets: props.order.conf_pets || false,
  conf_note_to_master: props.order.conf_note_to_master || '',
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

const submitReschedule = () => {
  rescheduleForm.post(route('admin.orders.reschedule', props.order.id), {
    onSuccess: () => {
      showRescheduleModal.value = false;
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

const submitConfirmation = () => {
  confirmationForm.post(route('admin.orders.save-confirmation', props.order.id), {
    onSuccess: () => {
      showConfirmationModal.value = false;
    },
  });
};

const getCallOutcomeClass = (outcome) => {
  const classes = {
    'pending': 'bg-gray-100 text-gray-800',
    'confirmed': 'bg-green-100 text-green-800',
    'reschedule': 'bg-yellow-100 text-yellow-800',
    'no_answer': 'bg-orange-100 text-orange-800',
    'cancelled': 'bg-red-100 text-red-800',
  };
  return classes[outcome] || 'bg-gray-100 text-gray-600';
};

const isReadyForTherapist = () => {
  return props.order.call_outcome === 'confirmed'
    && props.order.payment_status === 'PAID'
    && props.order.status === 'CONFIRMED'
    && props.order.address
    && !props.order.ready_sent_at;
};

const calculateGroupTotal = () => {
  if (!props.order.group_orders) return Number(props.order.total_amount).toLocaleString();
  
  const total = Number(props.order.total_amount) + 
    props.order.group_orders.reduce((sum, o) => sum + Number(o.total_amount || 0), 0);
  
  return total.toLocaleString();
};

const generatePaymentLink = async (provider) => {
  generatingPaymentLink.value = true;
  generatedPaymentLink.value = '';
  
  try {
    const response = await fetch(route('admin.orders.payment-link', props.order.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
      body: JSON.stringify({ provider }),
    });
    
    const data = await response.json();
    
    if (data.success) {
      generatedPaymentLink.value = data.link;
      paymentLinkProvider.value = provider;
    } else {
      alert(data.message || 'Xatolik yuz berdi');
    }
  } catch (e) {
    alert('Xatolik yuz berdi');
  } finally {
    generatingPaymentLink.value = false;
  }
};

const copyPaymentLink = async () => {
  if (!generatedPaymentLink.value) return;
  
  try {
    await navigator.clipboard.writeText(generatedPaymentLink.value);
    alert('Havola nusxalandi!');
  } catch (e) {
    // Fallback for older browsers
    const textArea = document.createElement('textarea');
    textArea.value = generatedPaymentLink.value;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Havola nusxalandi!');
  }
};

const canGeneratePaymentLink = () => {
  return props.order.payment_status !== 'PAID' && 
         props.order.status !== 'CANCELLED';
};

const hasConfiguredProvider = () => {
  return props.paymentProviders?.payme?.configured || props.paymentProviders?.click?.configured;
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

const getPaymentItemStatusClass = (status) => {
  const classes = {
    'PENDING': 'bg-yellow-100 text-yellow-800',
    'PROCESSING': 'bg-blue-100 text-blue-800',
    'PAID': 'bg-green-100 text-green-800',
    'CANCELLED': 'bg-gray-100 text-gray-800',
    'FAILED': 'bg-red-100 text-red-800',
    'REFUNDED': 'bg-purple-100 text-purple-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-600';
};

const getPaymentStatusLabel = (status) => {
  const labels = {
    'PENDING': 'Kutilmoqda',
    'PROCESSING': 'Jarayonda',
    'PAID': "To'langan",
    'CANCELLED': 'Bekor qilingan',
    'FAILED': 'Xatolik',
    'REFUNDED': 'Qaytarilgan',
  };
  return labels[status] || status;
};

const getProviderLabel = (provider) => {
  const labels = {
    'payme': 'Payme',
    'click': 'Click',
  };
  return labels[provider] || provider;
};

const formatAmount = (amount, currency = 'UZS') => {
  return Number(amount).toLocaleString() + ' ' + currency;
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ');
};

const formatDateTime = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleString('uz-UZ');
};

const formatArrivalWindow = (order) => {
  if (!order.arrival_window_start || !order.arrival_window_end) return '-';
  const start = order.arrival_window_start?.substring(0, 5);
  const end = order.arrival_window_end?.substring(0, 5);
  return `${start} – ${end}`;
};

const getStatusLabel = (status) => {
  return props.statusOptions?.find(s => s.value === status)?.label || status;
};

const getLogActionLabel = (action) => {
  const labels = {
    'status_changed': 'Status o\'zgartirildi',
    'rescheduled': 'Vaqt o\'zgartirildi',
    'note_added': 'Izoh qo\'shildi',
    'created': 'Buyurtma yaratildi',
  };
  return labels[action] || action;
};

// Reservation progress calculation (Block E)
const getReservationProgress = () => {
  let steps = 0;
  const total = 6;
  
  // Step 1: Order created - always complete
  steps++;
  
  // Step 2: Confirmation call
  if (props.order.call_outcome === 'confirmed') steps++;
  
  // Step 3: Payment
  if (props.order.payment_status === 'PAID') steps++;
  
  // Step 4: Ready notification sent
  if (props.order.ready_sent_at) steps++;
  
  // Step 5: Work order sent
  if (props.order.work_order_sent_at) steps++;
  
  // Step 6: Session started/completed
  if (props.order.status === 'IN_PROGRESS' || props.order.status === 'COMPLETED') steps++;
  
  return Math.round((steps / total) * 100);
};

// Work Order text (computed)
const workOrderText = computed(() => {
  const o = props.order;
  const lines = [
    `📋 НАРЯД #${o.order_number}`,
    `━━━━━━━━━━━━━━━━━━━━`,
    ``,
    `👤 Mijoz: ${o.customer?.name || 'Noma\'lum'}`,
    `📞 Tel: ${o.conf_onsite_phone || o.contact_phone || o.customer?.phone || '-'}`,
    ``,
    `📍 Manzil: ${o.address || '-'}`,
  ];
  
  if (o.conf_entrance || o.conf_floor) {
    lines.push(`   Kirish: ${o.conf_entrance || '-'}, Qavat: ${o.conf_floor || '-'}`);
  }
  if (o.conf_elevator) {
    lines.push(`   ✓ Lift bor`);
  }
  if (o.conf_landmark) {
    lines.push(`🗺 Mo'ljal: ${o.conf_landmark}`);
  }
  if (o.conf_parking) {
    lines.push(`🅿️ Parking: ${o.conf_parking}`);
  }
  
  lines.push(``);
  lines.push(`📅 Sana: ${formatDate(o.booking_date)}`);
  lines.push(`⏰ Kelish oynasi: ${formatArrivalWindow(o)}`);
  lines.push(``);
  lines.push(`💆 Xizmat: ${o.service_type?.name?.uz || o.service_type?.name || '-'}`);
  lines.push(`⏱ Davomiylik: ${o.duration?.duration || '-'} daqiqa`);
  
  if (o.oil) {
    lines.push(`🧴 Yog': ${o.oil?.name?.uz || o.oil?.name}`);
  }
  if (o.people_count > 1) {
    lines.push(`👥 Odamlar soni: ${o.people_count}`);
  }
  
  if (o.conf_constraints) {
    lines.push(``);
    lines.push(`⚠️ Cheklovlar: ${o.conf_constraints}`);
  }
  if (o.conf_pets) {
    lines.push(`🐾 Hayvonlar bor!`);
  }
  if (!o.conf_space_ok) {
    lines.push(`📐 2×2 joy yo'q - tekshiring!`);
  }
  
  if (o.conf_note_to_master) {
    lines.push(``);
    lines.push(`📝 Izoh: ${o.conf_note_to_master}`);
  }
  
  lines.push(``);
  lines.push(`━━━━━━━━━━━━━━━━━━━━`);
  lines.push(`💰 Summa: ${Number(o.total_amount).toLocaleString()} so'm`);
  lines.push(`💳 To'lov: ${o.payment_status === 'PAID' ? '✅ To\'langan' : '⏳ Kutilmoqda'}`);
  
  return lines.join('\n');
});

// Copy work order to clipboard
const copyWorkOrder = async () => {
  try {
    await navigator.clipboard.writeText(workOrderText.value);
    alert('Наряд nusxalandi!');
  } catch (e) {
    const textArea = document.createElement('textarea');
    textArea.value = workOrderText.value;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    alert('Наряд nusxalandi!');
  }
};

// Send work order to master via Telegram
const sendWorkOrderToMaster = async () => {
  if (!props.order.master?.telegram_chat_id) {
    alert('Master Telegram chat ID mavjud emas');
    return;
  }
  
  sendingWorkOrder.value = true;
  
  try {
    const response = await fetch(route('admin.orders.send-work-order', props.order.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
      },
    });
    
    const data = await response.json();
    
    if (data.success) {
      alert('Наряд masterga yuborildi!');
      router.reload();
    } else {
      alert(data.message || 'Xatolik yuz berdi');
    }
  } catch (e) {
    alert('Xatolik yuz berdi');
  } finally {
    sendingWorkOrder.value = false;
  }
};

// QA Form
const qaForm = useForm({
  qa_overall_rating: 5,
  qa_punctuality_rating: 5,
  qa_professionalism_rating: 5,
  qa_feedback: '',
});

const submitQa = () => {
  qaForm.post(route('admin.orders.save-qa', props.order.id), {
    onSuccess: () => {
      showQaModal.value = false;
    },
  });
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
        <!-- Group Orders Card (Multi-Master) -->
        <div v-if="order.booking_group_id && order.group_orders?.length > 0" class="bg-white rounded shadow-sm border-l-4 border-purple-500">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              <h3 class="font-semibold text-[#1f2d3d]">Guruh buyurtmasi</h3>
              <span class="px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-800 rounded">
                {{ order.group_orders.length + 1 }} kishi
              </span>
            </div>
          </div>
          <div class="p-4">
            <p class="text-sm text-[#6c757d] mb-3">Bu buyurtma guruh bronining bir qismi:</p>
            <div class="space-y-2">
              <!-- Current order -->
              <div class="flex items-center gap-3 p-2 bg-purple-50 rounded border border-purple-100">
                <span class="text-xs text-[#6c757d]">Hozirgi:</span>
                <span class="font-medium text-[#1f2d3d]">{{ order.master?.first_name }} {{ order.master?.last_name }}</span>
                <span class="text-xs text-[#6c757d]">→</span>
                <span class="text-sm">{{ order.service_type?.name?.uz || order.service_type?.name }}</span>
              </div>
              <!-- Linked orders -->
              <Link
                v-for="linkedOrder in order.group_orders"
                :key="linkedOrder.id"
                :href="route('admin.orders.show', linkedOrder.id)"
                class="flex items-center gap-3 p-2 bg-gray-50 rounded border border-gray-100 hover:bg-gray-100 transition"
              >
                <span class="text-xs font-mono text-[#007bff]">{{ linkedOrder.order_number }}</span>
                <span class="font-medium text-[#1f2d3d]">{{ linkedOrder.master?.first_name }} {{ linkedOrder.master?.last_name }}</span>
                <span class="text-xs text-[#6c757d]">→</span>
                <span class="text-sm">{{ linkedOrder.service_type?.name?.uz || linkedOrder.service_type?.name }}</span>
                <span
                  class="ml-auto px-2 py-0.5 text-xs font-medium rounded"
                  :class="getStatusClass(linkedOrder.status)"
                >
                  {{ getStatusLabel(linkedOrder.status) }}
                </span>
              </Link>
            </div>
          </div>
        </div>

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
              @click="showRescheduleModal = true"
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
                <p class="text-sm text-[#6c757d]">Kelish oynasi</p>
                <p class="font-medium text-[#1f2d3d]">{{ formatArrivalWindow(order) }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.service') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.service_type?.name?.uz || order.service_type?.name }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">Davomiylik</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.duration?.duration || '-' }} daqiqa</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.oil') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.oil?.name?.uz || order.oil?.name || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">Odamlar soni</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.people_count || 1 }} kishi</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.master') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.master?.first_name }} {{ order.master?.last_name }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">{{ t('orders.amount') }}</p>
                <p class="font-medium text-[#1f2d3d]">{{ Number(order.total_amount).toLocaleString() }} so'm</p>
                <p v-if="order.group_orders?.length > 0" class="text-xs text-[#6c757d] mt-1">
                  Guruh jami: {{ calculateGroupTotal() }} so'm
                </p>
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

        <!-- Block C: Confirmation Form (Анкета подтверждения) -->
        <div class="bg-white rounded shadow-sm">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <h3 class="font-semibold text-[#1f2d3d]">Анкета подтверждения</h3>
              <span
                v-if="order.call_outcome"
                class="px-2 py-1 text-xs font-medium rounded"
                :class="getCallOutcomeClass(order.call_outcome)"
              >
                {{ order.call_outcome_label }}
              </span>
            </div>
            <button
              @click="showConfirmationModal = true"
              class="text-sm text-[#007bff] hover:underline"
            >
              {{ order.call_outcome ? 'Tahrirlash' : 'To\'ldirish' }}
            </button>
          </div>
          <div class="p-4">
            <div v-if="order.call_outcome && order.call_outcome !== 'pending'" class="grid grid-cols-2 gap-4">
              <!-- Address details -->
              <div>
                <p class="text-sm text-[#6c757d]">Kirish</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_entrance || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">Qavat</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_floor || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">Lift</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_elevator ? 'Ha' : 'Yo\'q' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">Joydagi telefon</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_onsite_phone || '-' }}</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-[#6c757d]">Parking</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_parking || '-' }}</p>
              </div>
              <div class="col-span-2">
                <p class="text-sm text-[#6c757d]">Mo'ljal</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_landmark || '-' }}</p>
              </div>
              <!-- Restrictions -->
              <div class="col-span-2 pt-3 border-t border-gray-100">
                <p class="text-sm text-[#6c757d]">Cheklovlar</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_constraints || '-' }}</p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">2×2 joy bormi?</p>
                <p class="font-medium" :class="order.conf_space_ok ? 'text-green-600' : 'text-orange-600'">
                  {{ order.conf_space_ok ? 'Ha' : 'Yo\'q' }}
                </p>
              </div>
              <div>
                <p class="text-sm text-[#6c757d]">Hayvonlar</p>
                <p class="font-medium" :class="order.conf_pets ? 'text-orange-600' : 'text-green-600'">
                  {{ order.conf_pets ? 'Ha' : 'Yo\'q' }}
                </p>
              </div>
              <div v-if="order.conf_note_to_master" class="col-span-2">
                <p class="text-sm text-[#6c757d]">Ustaga izoh</p>
                <p class="font-medium text-[#1f2d3d]">{{ order.conf_note_to_master }}</p>
              </div>
              <!-- Ready status -->
              <div v-if="order.ready_sent_at" class="col-span-2 pt-3 border-t border-gray-100">
                <p class="text-sm text-green-600 flex items-center gap-2">
                  <span class="text-lg">✓</span>
                  Ustaga yuborildi: {{ formatDateTime(order.ready_sent_at) }}
                </p>
              </div>
              <div v-else-if="isReadyForTherapist()" class="col-span-2 pt-3 border-t border-gray-100">
                <p class="text-sm text-blue-600 flex items-center gap-2">
                  <span class="text-lg">📤</span>
                  Tayyor ustaga yuborishga
                </p>
              </div>
            </div>
            <div v-else class="text-center py-6">
              <p class="text-[#6c757d] mb-3">Buyurtmani tasdiqlash uchun anketa to'ldirilmagan</p>
              <button
                @click="showConfirmationModal = true"
                class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition"
              >
                Anketa to'ldirish
              </button>
            </div>
          </div>
        </div>

        <!-- Block E: Reservation Status (Bron holati) -->
        <div class="bg-white rounded shadow-sm border-l-4 border-green-500">
          <div class="px-4 py-3 border-b border-gray-200">
            <div class="flex items-center gap-2">
              <span class="text-xl">📋</span>
              <h3 class="font-semibold text-[#1f2d3d]">Bron holati</h3>
            </div>
          </div>
          <div class="p-4">
            <div class="space-y-3">
              <!-- Step 1: Order Created -->
              <div class="flex items-center gap-3">
                <span class="flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold bg-green-500">✓</span>
                <div class="flex-1">
                  <p class="text-sm font-medium text-[#1f2d3d]">Buyurtma yaratildi</p>
                  <p class="text-xs text-[#6c757d]">{{ formatDateTime(order.created_at) }}</p>
                </div>
              </div>

              <!-- Step 2: Confirmation Call -->
              <div class="flex items-center gap-3">
                <span 
                  class="flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold"
                  :class="order.call_outcome === 'confirmed' ? 'bg-green-500' : (order.call_outcome && order.call_outcome !== 'pending' ? 'bg-yellow-500' : 'bg-gray-300')"
                >
                  {{ order.call_outcome === 'confirmed' ? '✓' : (order.call_outcome && order.call_outcome !== 'pending' ? '!' : '2') }}
                </span>
                <div class="flex-1">
                  <p class="text-sm font-medium text-[#1f2d3d]">Tasdiq qo'ng'iroqi</p>
                  <p v-if="order.confirmed_at" class="text-xs text-[#6c757d]">
                    {{ formatDateTime(order.confirmed_at) }} 
                    <span v-if="order.confirmed_by_user">- {{ order.confirmed_by_user?.name }}</span>
                  </p>
                  <p v-else class="text-xs text-orange-600">Kutilmoqda</p>
                </div>
              </div>

              <!-- Step 3: Payment -->
              <div class="flex items-center gap-3">
                <span 
                  class="flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold"
                  :class="order.payment_status === 'PAID' ? 'bg-green-500' : 'bg-gray-300'"
                >
                  {{ order.payment_status === 'PAID' ? '✓' : '3' }}
                </span>
                <div class="flex-1">
                  <p class="text-sm font-medium text-[#1f2d3d]">To'lov</p>
                  <p v-if="order.paid_at" class="text-xs text-[#6c757d]">{{ formatDateTime(order.paid_at) }}</p>
                  <p v-else class="text-xs text-orange-600">To'lanmagan</p>
                </div>
              </div>

              <!-- Step 4: Ready notification sent to therapist -->
              <div class="flex items-center gap-3">
                <span 
                  class="flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold"
                  :class="order.ready_sent_at ? 'bg-green-500' : 'bg-gray-300'"
                >
                  {{ order.ready_sent_at ? '✓' : '4' }}
                </span>
                <div class="flex-1">
                  <p class="text-sm font-medium text-[#1f2d3d]">Ustalarga xabar</p>
                  <p v-if="order.ready_sent_at" class="text-xs text-[#6c757d]">{{ formatDateTime(order.ready_sent_at) }}</p>
                  <p v-else class="text-xs text-orange-600">Yuborilmagan</p>
                </div>
              </div>

              <!-- Step 5: Work order sent to master -->
              <div class="flex items-center gap-3">
                <span 
                  class="flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold"
                  :class="order.work_order_sent_at ? 'bg-green-500' : 'bg-gray-300'"
                >
                  {{ order.work_order_sent_at ? '✓' : '5' }}
                </span>
                <div class="flex-1">
                  <p class="text-sm font-medium text-[#1f2d3d]">Наряд yuborildi</p>
                  <p v-if="order.work_order_sent_at" class="text-xs text-[#6c757d]">{{ formatDateTime(order.work_order_sent_at) }}</p>
                  <p v-else class="text-xs text-orange-600">Yuborilmagan</p>
                </div>
              </div>

              <!-- Step 6: Session started (IN_PROGRESS) -->
              <div class="flex items-center gap-3">
                <span 
                  class="flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold"
                  :class="order.status === 'IN_PROGRESS' || order.status === 'COMPLETED' ? 'bg-green-500' : 'bg-gray-300'"
                >
                  {{ order.status === 'IN_PROGRESS' || order.status === 'COMPLETED' ? '✓' : '6' }}
                </span>
                <div class="flex-1">
                  <p class="text-sm font-medium text-[#1f2d3d]">Seans boshlandi</p>
                  <p v-if="order.status === 'IN_PROGRESS' || order.status === 'COMPLETED'" class="text-xs text-green-600">
                    {{ order.status === 'COMPLETED' ? 'Yakunlangan' : 'Jarayonda' }}
                  </p>
                  <p v-else class="text-xs text-[#6c757d]">Kutilmoqda</p>
                </div>
              </div>
            </div>

            <!-- Progress bar -->
            <div class="mt-4 pt-4 border-t border-gray-100">
              <div class="flex justify-between text-xs text-[#6c757d] mb-2">
                <span>Jarayon</span>
                <span>{{ getReservationProgress() }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="getReservationProgress() === 100 ? 'bg-green-500' : 'bg-blue-500'"
                  :style="{ width: getReservationProgress() + '%' }"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Block F: Work Order (Наряд) -->
        <div class="bg-white rounded shadow-sm border-l-4 border-blue-500">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-xl">📋</span>
              <h3 class="font-semibold text-[#1f2d3d]">Наряд (Work Order)</h3>
            </div>
            <div class="flex items-center gap-2">
              <button
                @click="copyWorkOrder"
                class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition flex items-center gap-1"
              >
                <span>📋</span> Nusxa olish
              </button>
              <button
                v-if="order.master?.telegram_chat_id"
                @click="sendWorkOrderToMaster"
                :disabled="sendingWorkOrder"
                class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded hover:bg-blue-700 transition flex items-center gap-1 disabled:opacity-50"
              >
                <span>📤</span> {{ sendingWorkOrder ? 'Yuborilmoqda...' : 'Masterga yuborish' }}
              </button>
            </div>
          </div>
          <div class="p-4">
            <div class="bg-gray-50 rounded-lg p-4 font-mono text-sm whitespace-pre-wrap border border-gray-200">{{ workOrderText }}</div>
            <div v-if="order.work_order_sent_at" class="mt-3 text-sm text-green-600 flex items-center gap-2">
              <span>✓</span> Masterga yuborildi: {{ formatDateTime(order.work_order_sent_at) }}
            </div>
          </div>
        </div>

        <!-- Block G: QA Section (Sifat nazorati) - Only for COMPLETED orders -->
        <div v-if="order.status === 'COMPLETED'" class="bg-white rounded shadow-sm border-l-4 border-yellow-500">
          <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="text-xl">⭐</span>
              <h3 class="font-semibold text-[#1f2d3d]">Sifat nazorati</h3>
            </div>
            <button
              v-if="!order.qa_completed"
              @click="showQaModal = true"
              class="text-sm text-[#007bff] hover:underline"
            >
              Baholash
            </button>
          </div>
          <div class="p-4">
            <div v-if="order.qa_completed" class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-[#6c757d]">Umumiy baho:</span>
                <div class="flex items-center gap-1">
                  <span v-for="i in 5" :key="i" class="text-lg" :class="i <= order.qa_overall_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-[#6c757d]">Vaqtga rioya:</span>
                <div class="flex items-center gap-1">
                  <span v-for="i in 5" :key="i" class="text-lg" :class="i <= order.qa_punctuality_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-sm text-[#6c757d]">Professionallik:</span>
                <div class="flex items-center gap-1">
                  <span v-for="i in 5" :key="i" class="text-lg" :class="i <= order.qa_professionalism_rating ? 'text-yellow-400' : 'text-gray-300'">★</span>
                </div>
              </div>
              <div v-if="order.qa_feedback" class="pt-3 border-t border-gray-100">
                <p class="text-sm text-[#6c757d]">Izoh:</p>
                <p class="text-sm text-[#1f2d3d]">{{ order.qa_feedback }}</p>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-sm text-[#6c757d] mb-3">Buyurtma hali baholanmagan</p>
              <button
                @click="showQaModal = true"
                class="px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded hover:bg-yellow-600 transition"
              >
                ⭐ Baholash
              </button>
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
            <div class="flex items-center justify-between mb-3">
              <span class="text-sm text-[#6c757d]">{{ t('orders.status') }}:</span>
              <span
                class="inline-flex px-3 py-1 text-sm font-medium rounded"
                :class="getPaymentStatusClass(order.payment_status)"
              >
                {{ order.payment_status === 'NOT_PAID' ? "To'lanmagan" : order.payment_status === 'PAID' ? "To'langan" : 'Qaytarilgan' }}
              </span>
            </div>

            <div class="flex items-center justify-between mb-3 pb-3 border-b border-gray-100">
              <span class="text-sm text-[#6c757d]">{{ t('orders.amount') }}:</span>
              <span class="font-semibold text-[#1f2d3d]">{{ formatAmount(order.total_amount) }}</span>
            </div>

            <!-- Payment Link Generator -->
            <div v-if="canGeneratePaymentLink()" class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
              <p class="text-sm font-medium text-blue-800 mb-2">To'lov havolasini yaratish</p>
              
              <!-- If providers are configured -->
              <div v-if="hasConfiguredProvider()" class="flex gap-2 mb-2">
                <button
                  v-if="paymentProviders?.payme?.configured"
                  @click="generatePaymentLink('payme')"
                  :disabled="generatingPaymentLink"
                  class="flex-1 px-3 py-2 text-xs font-medium bg-[#00CDCD] text-white rounded hover:bg-[#00B5B5] transition disabled:opacity-50"
                >
                  {{ generatingPaymentLink ? '...' : 'Payme' }}
                </button>
                <button
                  v-if="paymentProviders?.click?.configured"
                  @click="generatePaymentLink('click')"
                  :disabled="generatingPaymentLink"
                  class="flex-1 px-3 py-2 text-xs font-medium bg-[#0066FF] text-white rounded hover:bg-[#0052CC] transition disabled:opacity-50"
                >
                  {{ generatingPaymentLink ? '...' : 'Click' }}
                </button>
              </div>
              
              <!-- If not configured -->
              <div v-else class="text-xs text-orange-600 bg-orange-50 p-2 rounded">
                ⚠️ Payme/Click sozlanmagan. .env faylda PAYME_MERCHANT_ID yoki CLICK_MERCHANT_ID qo'shing.
              </div>
              
              <!-- Generated link -->
              <div v-if="generatedPaymentLink" class="mt-2">
                <div class="flex items-center gap-2">
                  <input
                    type="text"
                    :value="generatedPaymentLink"
                    readonly
                    class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded bg-white"
                  />
                  <button
                    @click="copyPaymentLink"
                    class="px-3 py-1.5 text-xs font-medium bg-green-600 text-white rounded hover:bg-green-700 transition"
                  >
                    Nusxa
                  </button>
                </div>
                <p class="text-xs text-green-700 mt-1">
                  {{ paymentLinkProvider === 'payme' ? 'Payme' : 'Click' }} havolasi tayyor
                </p>
              </div>
            </div>

            <div v-if="order.payments && order.payments.length > 0">
              <p class="text-sm font-medium text-[#1f2d3d] mb-2">{{ t('payments.history') }}</p>
              <div class="space-y-3">
                <div
                  v-for="payment in order.payments"
                  :key="payment.id"
                  class="p-3 bg-gray-50 rounded border border-gray-100"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-[#6c757d]">{{ getProviderLabel(payment.provider) }}</span>
                    <span
                      class="inline-flex px-2 py-0.5 text-xs font-medium rounded"
                      :class="getPaymentItemStatusClass(payment.status)"
                    >
                      {{ getPaymentStatusLabel(payment.status) }}
                    </span>
                  </div>
                  <div class="text-sm font-semibold text-[#1f2d3d]">
                    {{ formatAmount(payment.amount, payment.currency) }}
                  </div>
                  <div class="text-xs text-[#6c757d] mt-1">
                    ID: {{ payment.transaction_id }}
                  </div>
                  <div v-if="payment.paid_at" class="text-xs text-green-600 mt-1">
                    {{ t('orders.paidAt') }}: {{ formatDateTime(payment.paid_at) }}
                  </div>
                  <div v-if="payment.error_message" class="text-xs text-red-600 mt-1">
                    {{ payment.error_message }}
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="text-sm text-[#6c757d] italic">
              {{ t('payments.noPayments') }}
            </div>
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
                  <p class="text-sm font-medium text-[#1f2d3d]">{{ getLogActionLabel(log.action) }}</p>
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
          <button @click="showStatusModal = false" class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition">
            {{ t('common.cancel') }}
          </button>
          <button @click="submitStatus" :disabled="statusForm.processing" class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50">
            {{ t('common.save') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Reschedule Modal -->
    <div v-if="showRescheduleModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200">
          <h3 class="font-semibold text-[#1f2d3d]">{{ t('orders.changeTime') }}</h3>
        </div>
        <div class="p-4 space-y-4">
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.date') }}</label>
            <input
              v-model="rescheduleForm.booking_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Kelish boshlanishi</label>
              <input
                v-model="rescheduleForm.arrival_window_start"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Kelish tugashi</label>
              <input
                v-model="rescheduleForm.arrival_window_end"
                type="time"
                class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">{{ t('orders.comment') }}</label>
            <textarea
              v-model="rescheduleForm.comment"
              rows="2"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
            ></textarea>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button @click="showRescheduleModal = false" class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition">
            {{ t('common.cancel') }}
          </button>
          <button @click="submitReschedule" :disabled="rescheduleForm.processing" class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50">
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
          <button @click="showNoteModal = false" class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition">
            {{ t('common.cancel') }}
          </button>
          <button @click="submitNote" :disabled="noteForm.processing || !noteForm.note" class="px-4 py-2 bg-[#007bff] text-white text-sm font-medium rounded hover:bg-[#0069d9] transition disabled:opacity-50">
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
          <button @click="showCancelModal = false" class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition">
            {{ t('common.cancel') }}
          </button>
          <button @click="submitCancel" :disabled="cancelForm.processing || !cancelForm.reason" class="px-4 py-2 bg-[#dc3545] text-white text-sm font-medium rounded hover:bg-[#c82333] transition disabled:opacity-50">
            {{ t('orders.confirmCancel') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Confirmation Form Modal (Block C) -->
    <div v-if="showConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 overflow-y-auto p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl my-8">
        <div class="px-4 py-3 border-b border-gray-200 bg-[#28a745] rounded-t-lg">
          <h3 class="font-semibold text-white">Анкета подтверждения</h3>
        </div>
        <div class="p-4 max-h-[70vh] overflow-y-auto">
          <!-- Call Outcome -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[#1f2d3d] mb-2">Qo'ng'iroq natijasi *</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
              <button
                v-for="option in callOutcomeOptions"
                :key="option.value"
                @click="confirmationForm.call_outcome = option.value"
                type="button"
                class="px-3 py-2 text-sm font-medium rounded border transition"
                :class="confirmationForm.call_outcome === option.value
                  ? 'border-[#007bff] bg-[#007bff] text-white'
                  : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
              >
                {{ option.label }}
              </button>
            </div>
          </div>

          <!-- Address Details -->
          <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <h4 class="font-medium text-[#1f2d3d] mb-4">Manzil tafsilotlari</h4>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Kirish (Pod)</label>
                <input
                  v-model="confirmationForm.conf_entrance"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                  placeholder="1"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Qavat</label>
                <input
                  v-model="confirmationForm.conf_floor"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                  placeholder="3"
                />
              </div>
              <div class="col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="confirmationForm.conf_elevator"
                    type="checkbox"
                    class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                  />
                  <span class="text-sm font-medium text-[#1f2d3d]">Lift bor</span>
                </label>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Parking</label>
                <input
                  v-model="confirmationForm.conf_parking"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                  placeholder="Hovlida / Ko'chada / ..."
                />
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Mo'ljal</label>
                <input
                  v-model="confirmationForm.conf_landmark"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                  placeholder="Makro yonida, 5-uy"
                />
              </div>
            </div>
          </div>

          <!-- Contact -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Joydagi telefon</label>
            <input
              v-model="confirmationForm.conf_onsite_phone"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              placeholder="+998 90 123 45 67"
            />
          </div>

          <!-- Restrictions -->
          <div class="mb-6 p-4 bg-orange-50 rounded-lg border border-orange-200">
            <h4 class="font-medium text-orange-800 mb-4">Muhim ma'lumotlar</h4>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Cheklovlar (allergia, sog'liq, ...)</label>
                <textarea
                  v-model="confirmationForm.conf_constraints"
                  rows="2"
                  class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
                  placeholder="Oyoqqa massaj qilmang, ..."
                ></textarea>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="confirmationForm.conf_space_ok"
                    type="checkbox"
                    class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                  />
                  <span class="text-sm font-medium text-[#1f2d3d]">2×2 metr joy bor</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                  <input
                    v-model="confirmationForm.conf_pets"
                    type="checkbox"
                    class="w-4 h-4 text-[#007bff] border-gray-300 rounded focus:ring-[#007bff]"
                  />
                  <span class="text-sm font-medium text-[#1f2d3d]">Hayvonlar bor</span>
                </label>
              </div>
            </div>
          </div>

          <!-- Note to Master -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Ustaga izoh</label>
            <textarea
              v-model="confirmationForm.conf_note_to_master"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              placeholder="Qo'shimcha ma'lumotlar..."
            ></textarea>
          </div>

          <!-- Payment Link (if unpaid) -->
          <div v-if="order.payment_status !== 'PAID' && canGeneratePaymentLink()" class="p-4 bg-blue-50 rounded-lg border border-blue-200">
            <h4 class="font-medium text-blue-800 mb-3">To'lov havolasini yuborish</h4>
            
            <!-- If providers configured -->
            <div v-if="hasConfiguredProvider()" class="flex gap-2 mb-2">
              <button
                v-if="paymentProviders?.payme?.configured"
                type="button"
                @click="generatePaymentLink('payme')"
                :disabled="generatingPaymentLink"
                class="flex-1 px-3 py-2 text-sm font-medium bg-[#00CDCD] text-white rounded hover:bg-[#00B5B5] transition disabled:opacity-50"
              >
                {{ generatingPaymentLink ? 'Yuklanmoqda...' : 'Payme havola' }}
              </button>
              <button
                v-if="paymentProviders?.click?.configured"
                type="button"
                @click="generatePaymentLink('click')"
                :disabled="generatingPaymentLink"
                class="flex-1 px-3 py-2 text-sm font-medium bg-[#0066FF] text-white rounded hover:bg-[#0052CC] transition disabled:opacity-50"
              >
                {{ generatingPaymentLink ? 'Yuklanmoqda...' : 'Click havola' }}
              </button>
            </div>
            
            <!-- Not configured -->
            <div v-else class="text-sm text-orange-600 bg-orange-50 p-3 rounded">
              ⚠️ Payme/Click sozlanmagan. .env faylda merchant ID qo'shing.
            </div>
            
            <div v-if="generatedPaymentLink" class="mt-3">
              <div class="flex items-center gap-2">
                <input
                  type="text"
                  :value="generatedPaymentLink"
                  readonly
                  class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded bg-white font-mono"
                />
                <button
                  type="button"
                  @click="copyPaymentLink"
                  class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded hover:bg-green-700 transition"
                >
                  Nusxalash
                </button>
              </div>
              <p class="text-sm text-green-700 mt-2 flex items-center gap-1">
                <span class="text-lg">✓</span>
                {{ paymentLinkProvider === 'payme' ? 'Payme' : 'Click' }} to'lov havolasi tayyor - mijozga yuboring
              </p>
            </div>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button @click="showConfirmationModal = false" class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition">
            Bekor qilish
          </button>
          <button
            @click="submitConfirmation"
            :disabled="confirmationForm.processing"
            class="px-4 py-2 bg-[#28a745] text-white text-sm font-medium rounded hover:bg-[#218838] transition disabled:opacity-50"
          >
            Saqlash
          </button>
        </div>
      </div>
    </div>

    <!-- QA Rating Modal -->
    <div v-if="showQaModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="px-4 py-3 border-b border-gray-200 bg-yellow-500 rounded-t-lg">
          <h3 class="font-semibold text-white">⭐ Sifat nazorati</h3>
        </div>
        <div class="p-4 space-y-4">
          <!-- Overall Rating -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-2">Umumiy baho</label>
            <div class="flex items-center gap-1">
              <button
                v-for="i in 5"
                :key="i"
                type="button"
                @click="qaForm.qa_overall_rating = i"
                class="text-2xl transition hover:scale-110"
                :class="i <= qaForm.qa_overall_rating ? 'text-yellow-400' : 'text-gray-300'"
              >
                ★
              </button>
            </div>
          </div>
          
          <!-- Punctuality Rating -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-2">Vaqtga rioya qilish</label>
            <div class="flex items-center gap-1">
              <button
                v-for="i in 5"
                :key="i"
                type="button"
                @click="qaForm.qa_punctuality_rating = i"
                class="text-2xl transition hover:scale-110"
                :class="i <= qaForm.qa_punctuality_rating ? 'text-yellow-400' : 'text-gray-300'"
              >
                ★
              </button>
            </div>
          </div>
          
          <!-- Professionalism Rating -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-2">Professionallik</label>
            <div class="flex items-center gap-1">
              <button
                v-for="i in 5"
                :key="i"
                type="button"
                @click="qaForm.qa_professionalism_rating = i"
                class="text-2xl transition hover:scale-110"
                :class="i <= qaForm.qa_professionalism_rating ? 'text-yellow-400' : 'text-gray-300'"
              >
                ★
              </button>
            </div>
          </div>
          
          <!-- Feedback -->
          <div>
            <label class="block text-sm font-medium text-[#1f2d3d] mb-1">Izoh</label>
            <textarea
              v-model="qaForm.qa_feedback"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded text-sm focus:ring-[#007bff] focus:border-[#007bff]"
              placeholder="Qo'shimcha fikrlar..."
            ></textarea>
          </div>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 flex justify-end gap-3">
          <button @click="showQaModal = false" class="px-4 py-2 text-[#6c757d] hover:text-[#1f2d3d] text-sm font-medium transition">
            Bekor qilish
          </button>
          <button
            @click="submitQa"
            :disabled="qaForm.processing"
            class="px-4 py-2 bg-yellow-500 text-white text-sm font-medium rounded hover:bg-yellow-600 transition disabled:opacity-50"
          >
            Saqlash
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
