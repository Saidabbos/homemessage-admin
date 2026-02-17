<script setup lang="ts">
import { ref, computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Checkbox } from '@/components/ui/checkbox';
import { Separator } from '@/components/ui/separator';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
  order: any;
  availableStatuses: string[];
  statusOptions: Array<{ value: string; label: string }>;
  callOutcomeOptions: Array<{ value: string; label: string }>;
  paymentProviders: { payme?: { configured: boolean }; click?: { configured: boolean } };
}>();

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
const statusForm = useForm({ status: '', comment: '' });
const rescheduleForm = useForm({
  booking_date: props.order.booking_date?.split('T')[0] || '',
  arrival_window_start: props.order.arrival_window_start?.substring(0, 5) || '',
  arrival_window_end: props.order.arrival_window_end?.substring(0, 5) || '',
  comment: '',
});
const noteForm = useForm({ note: '' });
const cancelForm = useForm({ reason: '' });
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
const qaForm = useForm({
  qa_overall_rating: 5,
  qa_punctuality_rating: 5,
  qa_professionalism_rating: 5,
  qa_feedback: '',
});

// Actions
const openStatusModal = (status: string) => {
  statusForm.status = status;
  statusForm.comment = '';
  showStatusModal.value = true;
};

const submitStatus = () => {
  statusForm.post(route('admin.orders.update-status', props.order.id), {
    onSuccess: () => { showStatusModal.value = false; statusForm.reset(); },
  });
};

const submitReschedule = () => {
  rescheduleForm.post(route('admin.orders.reschedule', props.order.id), {
    onSuccess: () => { showRescheduleModal.value = false; },
  });
};

const submitNote = () => {
  noteForm.post(route('admin.orders.add-note', props.order.id), {
    onSuccess: () => { showNoteModal.value = false; noteForm.reset(); },
  });
};

const submitCancel = () => {
  cancelForm.post(route('admin.orders.cancel', props.order.id), {
    onSuccess: () => { showCancelModal.value = false; cancelForm.reset(); },
  });
};

const submitConfirmation = () => {
  confirmationForm.post(route('admin.orders.save-confirmation', props.order.id), {
    onSuccess: () => { showConfirmationModal.value = false; },
  });
};

const submitQa = () => {
  qaForm.post(route('admin.orders.save-qa', props.order.id), {
    onSuccess: () => { showQaModal.value = false; },
  });
};

// Helpers
const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
  const variants: Record<string, 'default' | 'secondary' | 'destructive' | 'outline'> = {
    'NEW': 'default', 'CONFIRMING': 'secondary', 'CONFIRMED': 'default',
    'IN_PROGRESS': 'secondary', 'COMPLETED': 'outline', 'CANCELLED': 'destructive',
  };
  return variants[status] || 'outline';
};

const getCallOutcomeVariant = (outcome: string) => {
  const variants: Record<string, string> = {
    'pending': 'bg-muted text-muted-foreground',
    'confirmed': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    'reschedule': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    'no_answer': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
    'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
  };
  return variants[outcome] || 'bg-muted text-muted-foreground';
};

const isReadyForTherapist = () => {
  return props.order.call_outcome === 'confirmed' && props.order.payment_status === 'PAID'
    && props.order.status === 'CONFIRMED' && props.order.address && !props.order.ready_sent_at;
};

const calculateGroupTotal = () => {
  if (!props.order.group_orders) return Number(props.order.total_amount).toLocaleString();
  const total = Number(props.order.total_amount) + props.order.group_orders.reduce((sum: number, o: any) => sum + Number(o.total_amount || 0), 0);
  return total.toLocaleString();
};

const generatePaymentLink = async (provider: string) => {
  generatingPaymentLink.value = true;
  generatedPaymentLink.value = '';
  try {
    const response = await fetch(route('admin.orders.payment-link', props.order.id), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
      body: JSON.stringify({ provider }),
    });
    const data = await response.json();
    if (data.success) { generatedPaymentLink.value = data.link; paymentLinkProvider.value = provider; }
    else { alert(data.message || 'Xatolik yuz berdi'); }
  } catch (e) { alert('Xatolik yuz berdi'); }
  finally { generatingPaymentLink.value = false; }
};

const copyPaymentLink = async () => {
  if (!generatedPaymentLink.value) return;
  try { await navigator.clipboard.writeText(generatedPaymentLink.value); alert('Havola nusxalandi!'); }
  catch (e) { const ta = document.createElement('textarea'); ta.value = generatedPaymentLink.value; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); alert('Havola nusxalandi!'); }
};

const canGeneratePaymentLink = () => props.order.payment_status !== 'PAID' && props.order.status !== 'CANCELLED';
const hasConfiguredProvider = () => props.paymentProviders?.payme?.configured || props.paymentProviders?.click?.configured;

const formatAmount = (amount: number, currency = 'UZS') => Number(amount).toLocaleString() + ' ' + currency;
const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('uz-UZ') : '-';
const formatDateTime = (date: string) => date ? new Date(date).toLocaleString('uz-UZ') : '-';
const formatArrivalWindow = (order: any) => {
  if (!order.arrival_window_start || !order.arrival_window_end) return '-';
  return `${order.arrival_window_start?.substring(0, 5)} – ${order.arrival_window_end?.substring(0, 5)}`;
};
const getStatusLabel = (status: string) => props.statusOptions?.find(s => s.value === status)?.label || status;
const getLogActionLabel = (action: string) => {
  const labels: Record<string, string> = { 'status_changed': 'Status o\'zgartirildi', 'rescheduled': 'Vaqt o\'zgartirildi', 'note_added': 'Izoh qo\'shildi', 'created': 'Buyurtma yaratildi' };
  return labels[action] || action;
};

const getReservationProgress = () => {
  let steps = 1; // Order created
  if (props.order.call_outcome === 'confirmed') steps++;
  if (props.order.payment_status === 'PAID') steps++;
  if (props.order.ready_sent_at) steps++;
  if (props.order.work_order_sent_at) steps++;
  if (props.order.status === 'IN_PROGRESS' || props.order.status === 'COMPLETED') steps++;
  return Math.round((steps / 6) * 100);
};

const workOrderText = computed(() => {
  const o = props.order;
  const lines = [
    `📋 НАРЯД #${o.order_number}`, `━━━━━━━━━━━━━━━━━━━━`, ``,
    `👤 Mijoz: ${o.customer?.name || 'Noma\'lum'}`,
    `📞 Tel: ${o.conf_onsite_phone || o.contact_phone || o.customer?.phone || '-'}`, ``,
    `📍 Manzil: ${o.address || '-'}`,
  ];
  if (o.conf_entrance || o.conf_floor) lines.push(`   Kirish: ${o.conf_entrance || '-'}, Qavat: ${o.conf_floor || '-'}`);
  if (o.conf_elevator) lines.push(`   ✓ Lift bor`);
  if (o.conf_landmark) lines.push(`🗺 Mo'ljal: ${o.conf_landmark}`);
  if (o.conf_parking) lines.push(`🅿️ Parking: ${o.conf_parking}`);
  lines.push(``, `📅 Sana: ${formatDate(o.booking_date)}`, `⏰ Kelish oynasi: ${formatArrivalWindow(o)}`, ``);
  lines.push(`💆 Xizmat: ${o.service_type?.name?.uz || o.service_type?.name || '-'}`, `⏱ Davomiylik: ${o.duration?.duration || '-'} daqiqa`);
  if (o.oil) lines.push(`🧴 Yog': ${o.oil?.name?.uz || o.oil?.name}`);
  if (o.people_count > 1) lines.push(`👥 Odamlar soni: ${o.people_count}`);
  if (o.conf_constraints) lines.push(``, `⚠️ Cheklovlar: ${o.conf_constraints}`);
  if (o.conf_pets) lines.push(`🐾 Hayvonlar bor!`);
  if (!o.conf_space_ok) lines.push(`📐 2×2 joy yo'q - tekshiring!`);
  if (o.conf_note_to_master) lines.push(``, `📝 Izoh: ${o.conf_note_to_master}`);
  lines.push(``, `━━━━━━━━━━━━━━━━━━━━`, `💰 Summa: ${Number(o.total_amount).toLocaleString()} so'm`);
  lines.push(`💳 To'lov: ${o.payment_status === 'PAID' ? '✅ To\'langan' : '⏳ Kutilmoqda'}`);
  return lines.join('\n');
});

const copyWorkOrder = async () => {
  try { await navigator.clipboard.writeText(workOrderText.value); alert('Наряд nusxalandi!'); }
  catch (e) { const ta = document.createElement('textarea'); ta.value = workOrderText.value; document.body.appendChild(ta); ta.select(); document.execCommand('copy'); document.body.removeChild(ta); alert('Наряд nusxalandi!'); }
};

const sendWorkOrderToMaster = async () => {
  if (!props.order.master?.telegram_chat_id) { alert('Master Telegram chat ID mavjud emas'); return; }
  sendingWorkOrder.value = true;
  try {
    const response = await fetch(route('admin.orders.send-work-order', props.order.id), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
    });
    const data = await response.json();
    if (data.success) { alert('Наряд masterga yuborildi!'); router.reload(); }
    else { alert(data.message || 'Xatolik yuz berdi'); }
  } catch (e) { alert('Xatolik yuz berdi'); }
  finally { sendingWorkOrder.value = false; }
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('orders.orderDetails', 'Buyurtma tafsilotlari') }}</h1>
        <p class="text-muted-foreground font-mono">{{ order.order_number }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.orders.index')">{{ t('common.back', 'Orqaga') }}</Link>
      </Button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Group Orders (Multi-Master) -->
        <Card v-if="order.booking_group_id && order.group_orders?.length > 0" class="border-l-4 border-l-purple-500">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Guruh buyurtmasi
              <Badge variant="secondary">{{ order.group_orders.length + 1 }} kishi</Badge>
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <div class="flex items-center gap-3 p-2 bg-purple-50 dark:bg-purple-950 rounded border border-purple-200 dark:border-purple-800">
              <span class="text-xs text-muted-foreground">Hozirgi:</span>
              <span class="font-medium">{{ order.master?.first_name }} {{ order.master?.last_name }}</span>
              <span class="text-muted-foreground">→</span>
              <span class="text-sm">{{ order.service_type?.name?.uz || order.service_type?.name }}</span>
            </div>
            <Link v-for="lo in order.group_orders" :key="lo.id" :href="route('admin.orders.show', lo.id)"
              class="flex items-center gap-3 p-2 rounded border hover:bg-muted/50 transition">
              <span class="text-xs font-mono text-primary">{{ lo.order_number }}</span>
              <span class="font-medium">{{ lo.master?.first_name }} {{ lo.master?.last_name }}</span>
              <span class="text-muted-foreground">→</span>
              <span class="text-sm">{{ lo.service_type?.name?.uz || lo.service_type?.name }}</span>
              <Badge :variant="getStatusVariant(lo.status)" class="ml-auto">{{ getStatusLabel(lo.status) }}</Badge>
            </Link>
          </CardContent>
        </Card>

        <!-- Order Status -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle>{{ t('orders.status', 'Status') }}</CardTitle>
            <Badge :variant="getStatusVariant(order.status)" class="text-sm">{{ getStatusLabel(order.status) }}</Badge>
          </CardHeader>
          <CardContent>
            <div class="flex flex-wrap gap-2">
              <Button v-for="status in availableStatuses" :key="status" variant="outline" size="sm"
                @click="openStatusModal(status)"
                :class="{ 'border-green-500 text-green-700 hover:bg-green-50 dark:hover:bg-green-950': status === 'CONFIRMED' || status === 'COMPLETED',
                          'border-yellow-500 text-yellow-700 hover:bg-yellow-50 dark:hover:bg-yellow-950': status === 'CONFIRMING' || status === 'IN_PROGRESS',
                          'border-red-500 text-red-700 hover:bg-red-50 dark:hover:bg-red-950': status === 'CANCELLED' }">
                {{ getStatusLabel(status) }}
              </Button>
              <Button v-if="order.status !== 'CANCELLED' && order.status !== 'COMPLETED'"
                variant="outline" size="sm" class="border-destructive text-destructive"
                @click="showCancelModal = true">
                {{ t('orders.cancel', 'Bekor qilish') }}
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Booking Info -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle>{{ t('orders.bookingInfo', 'Bron ma\'lumotlari') }}</CardTitle>
            <Button v-if="order.status !== 'CANCELLED' && order.status !== 'COMPLETED'"
              variant="link" size="sm" @click="showRescheduleModal = true">
              {{ t('orders.changeTime', 'Vaqtni o\'zgartirish') }}
            </Button>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-4">
              <div><p class="text-sm text-muted-foreground">{{ t('orders.date', 'Sana') }}</p><p class="font-medium">{{ formatDate(order.booking_date) }}</p></div>
              <div><p class="text-sm text-muted-foreground">Kelish oynasi</p><p class="font-medium">{{ formatArrivalWindow(order) }}</p></div>
              <div><p class="text-sm text-muted-foreground">{{ t('orders.service', 'Xizmat') }}</p><p class="font-medium">{{ order.service_type?.name?.uz || order.service_type?.name }}</p></div>
              <div><p class="text-sm text-muted-foreground">Davomiylik</p><p class="font-medium">{{ order.duration?.duration || '-' }} daqiqa</p></div>
              <div><p class="text-sm text-muted-foreground">{{ t('orders.oil', 'Yog\'') }}</p><p class="font-medium">{{ order.oil?.name?.uz || order.oil?.name || '-' }}</p></div>
              <div><p class="text-sm text-muted-foreground">Odamlar soni</p><p class="font-medium">{{ order.people_count || 1 }} kishi</p></div>
              <div><p class="text-sm text-muted-foreground">{{ t('orders.master', 'Master') }}</p><p class="font-medium">{{ order.master?.first_name }} {{ order.master?.last_name }}</p></div>
              <div><p class="text-sm text-muted-foreground">{{ t('orders.amount', 'Summa') }}</p><p class="font-medium">{{ Number(order.total_amount).toLocaleString() }} so'm</p>
                <p v-if="order.group_orders?.length > 0" class="text-xs text-muted-foreground">Guruh jami: {{ calculateGroupTotal() }} so'm</p></div>
            </div>
          </CardContent>
        </Card>

        <!-- Customer Info -->
        <Card>
          <CardHeader><CardTitle>{{ t('orders.customerInfo', 'Mijoz ma\'lumotlari') }}</CardTitle></CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 gap-4">
              <div><p class="text-sm text-muted-foreground">{{ t('customers.name', 'Ism') }}</p><p class="font-medium">{{ order.customer?.name || '-' }}</p></div>
              <div><p class="text-sm text-muted-foreground">{{ t('customers.phone', 'Telefon') }}</p><p class="font-medium">{{ order.customer?.phone || order.contact_phone || '-' }}</p></div>
              <div class="col-span-2"><p class="text-sm text-muted-foreground">{{ t('orders.address', 'Manzil') }}</p><p class="font-medium">{{ order.address || '-' }}</p>
                <p v-if="order.entrance || order.floor || order.apartment" class="text-sm text-muted-foreground">
                  <span v-if="order.entrance">Pod: {{ order.entrance }}</span><span v-if="order.floor">, Qavat: {{ order.floor }}</span><span v-if="order.apartment">, Xonadon: {{ order.apartment }}</span>
                </p></div>
              <div v-if="order.landmark" class="col-span-2"><p class="text-sm text-muted-foreground">{{ t('orders.landmark', 'Mo\'ljal') }}</p><p class="font-medium">{{ order.landmark }}</p></div>
            </div>
          </CardContent>
        </Card>

        <!-- Confirmation Form -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <div class="flex items-center gap-3">
              <CardTitle>Анкета подтверждения</CardTitle>
              <span v-if="order.call_outcome" :class="['px-2 py-1 text-xs font-medium rounded', getCallOutcomeVariant(order.call_outcome)]">{{ order.call_outcome_label }}</span>
            </div>
            <Button variant="link" size="sm" @click="showConfirmationModal = true">{{ order.call_outcome ? 'Tahrirlash' : 'To\'ldirish' }}</Button>
          </CardHeader>
          <CardContent>
            <div v-if="order.call_outcome && order.call_outcome !== 'pending'" class="grid grid-cols-2 gap-4">
              <div><p class="text-sm text-muted-foreground">Kirish</p><p class="font-medium">{{ order.conf_entrance || '-' }}</p></div>
              <div><p class="text-sm text-muted-foreground">Qavat</p><p class="font-medium">{{ order.conf_floor || '-' }}</p></div>
              <div><p class="text-sm text-muted-foreground">Lift</p><p class="font-medium">{{ order.conf_elevator ? 'Ha' : 'Yo\'q' }}</p></div>
              <div><p class="text-sm text-muted-foreground">Joydagi telefon</p><p class="font-medium">{{ order.conf_onsite_phone || '-' }}</p></div>
              <div class="col-span-2"><p class="text-sm text-muted-foreground">Parking</p><p class="font-medium">{{ order.conf_parking || '-' }}</p></div>
              <div class="col-span-2"><p class="text-sm text-muted-foreground">Mo'ljal</p><p class="font-medium">{{ order.conf_landmark || '-' }}</p></div>
              <Separator class="col-span-2" />
              <div class="col-span-2"><p class="text-sm text-muted-foreground">Cheklovlar</p><p class="font-medium">{{ order.conf_constraints || '-' }}</p></div>
              <div><p class="text-sm text-muted-foreground">2×2 joy bormi?</p><p :class="order.conf_space_ok ? 'text-green-600' : 'text-orange-600'" class="font-medium">{{ order.conf_space_ok ? 'Ha' : 'Yo\'q' }}</p></div>
              <div><p class="text-sm text-muted-foreground">Hayvonlar</p><p :class="order.conf_pets ? 'text-orange-600' : 'text-green-600'" class="font-medium">{{ order.conf_pets ? 'Ha' : 'Yo\'q' }}</p></div>
              <div v-if="order.conf_note_to_master" class="col-span-2"><p class="text-sm text-muted-foreground">Ustaga izoh</p><p class="font-medium">{{ order.conf_note_to_master }}</p></div>
              <div v-if="order.ready_sent_at" class="col-span-2 pt-3 border-t"><p class="text-sm text-green-600 flex items-center gap-2">✓ Ustaga yuborildi: {{ formatDateTime(order.ready_sent_at) }}</p></div>
              <div v-else-if="isReadyForTherapist()" class="col-span-2 pt-3 border-t"><p class="text-sm text-blue-600 flex items-center gap-2">📤 Tayyor ustaga yuborishga</p></div>
            </div>
            <div v-else class="text-center py-6">
              <p class="text-muted-foreground mb-3">Buyurtmani tasdiqlash uchun anketa to'ldirilmagan</p>
              <Button @click="showConfirmationModal = true">Anketa to'ldirish</Button>
            </div>
          </CardContent>
        </Card>

        <!-- Reservation Status -->
        <Card class="border-l-4 border-l-green-500">
          <CardHeader><CardTitle class="flex items-center gap-2">📋 Bron holati</CardTitle></CardHeader>
          <CardContent class="space-y-3">
            <div v-for="(step, i) in [
              { done: true, label: 'Buyurtma yaratildi', date: order.created_at },
              { done: order.call_outcome === 'confirmed', label: 'Tasdiq qo\'ng\'iroqi', date: order.confirmed_at },
              { done: order.payment_status === 'PAID', label: 'To\'lov', date: order.paid_at },
              { done: !!order.ready_sent_at, label: 'Ustalarga xabar', date: order.ready_sent_at },
              { done: !!order.work_order_sent_at, label: 'Наряд yuborildi', date: order.work_order_sent_at },
              { done: order.status === 'IN_PROGRESS' || order.status === 'COMPLETED', label: 'Seans boshlandi', date: null }
            ]" :key="i" class="flex items-center gap-3">
              <span :class="['flex items-center justify-center w-6 h-6 rounded-full text-white text-xs font-bold', step.done ? 'bg-green-500' : 'bg-muted-foreground/30']">
                {{ step.done ? '✓' : i + 1 }}
              </span>
              <div class="flex-1">
                <p class="text-sm font-medium">{{ step.label }}</p>
                <p v-if="step.date" class="text-xs text-muted-foreground">{{ formatDateTime(step.date) }}</p>
                <p v-else-if="!step.done" class="text-xs text-orange-600">Kutilmoqda</p>
              </div>
            </div>
            <Separator />
            <div class="flex justify-between text-xs text-muted-foreground mb-2"><span>Jarayon</span><span>{{ getReservationProgress() }}%</span></div>
            <Progress :model-value="getReservationProgress()" class="h-2" />
          </CardContent>
        </Card>

        <!-- Work Order -->
        <Card class="border-l-4 border-l-blue-500">
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="flex items-center gap-2">📋 Наряд (Work Order)</CardTitle>
            <div class="flex items-center gap-2">
              <Button variant="outline" size="sm" @click="copyWorkOrder">📋 Nusxa</Button>
              <Button v-if="order.master?.telegram_chat_id" size="sm" @click="sendWorkOrderToMaster" :disabled="sendingWorkOrder">
                📤 {{ sendingWorkOrder ? 'Yuborilmoqda...' : 'Masterga yuborish' }}
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <pre class="bg-muted rounded-lg p-4 font-mono text-sm whitespace-pre-wrap border">{{ workOrderText }}</pre>
            <p v-if="order.work_order_sent_at" class="mt-3 text-sm text-green-600 flex items-center gap-2">✓ Masterga yuborildi: {{ formatDateTime(order.work_order_sent_at) }}</p>
          </CardContent>
        </Card>

        <!-- QA Section (only for COMPLETED) -->
        <Card v-if="order.status === 'COMPLETED'" class="border-l-4 border-l-yellow-500">
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="flex items-center gap-2">⭐ Sifat nazorati</CardTitle>
            <Button v-if="!order.qa_completed" variant="link" size="sm" @click="showQaModal = true">Baholash</Button>
          </CardHeader>
          <CardContent>
            <div v-if="order.qa_completed" class="space-y-3">
              <div class="flex items-center justify-between"><span class="text-sm text-muted-foreground">Umumiy baho:</span>
                <div class="flex"><span v-for="i in 5" :key="i" :class="i <= order.qa_overall_rating ? 'text-yellow-400' : 'text-muted'">★</span></div></div>
              <div class="flex items-center justify-between"><span class="text-sm text-muted-foreground">Vaqtga rioya:</span>
                <div class="flex"><span v-for="i in 5" :key="i" :class="i <= order.qa_punctuality_rating ? 'text-yellow-400' : 'text-muted'">★</span></div></div>
              <div class="flex items-center justify-between"><span class="text-sm text-muted-foreground">Professionallik:</span>
                <div class="flex"><span v-for="i in 5" :key="i" :class="i <= order.qa_professionalism_rating ? 'text-yellow-400' : 'text-muted'">★</span></div></div>
              <div v-if="order.qa_feedback" class="pt-3 border-t"><p class="text-sm text-muted-foreground">Izoh:</p><p class="text-sm">{{ order.qa_feedback }}</p></div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-sm text-muted-foreground mb-3">Buyurtma hali baholanmagan</p>
              <Button variant="secondary" @click="showQaModal = true">⭐ Baholash</Button>
            </div>
          </CardContent>
        </Card>

        <!-- Dispatcher Notes -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle>{{ t('orders.dispatcherNotes', 'Dispetcher izohlari') }}</CardTitle>
            <Button variant="link" size="sm" @click="showNoteModal = true">{{ t('orders.addNote', 'Izoh qo\'shish') }}</Button>
          </CardHeader>
          <CardContent>
            <div v-if="order.dispatcher_notes" class="whitespace-pre-wrap text-sm">{{ order.dispatcher_notes }}</div>
            <p v-else class="text-sm text-muted-foreground italic">{{ t('orders.noNotes', 'Izohlar yo\'q') }}</p>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Payment Status -->
        <Card>
          <CardHeader><CardTitle>{{ t('orders.payment', 'To\'lov') }}</CardTitle></CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-muted-foreground">{{ t('orders.status', 'Status') }}:</span>
              <Badge :variant="order.payment_status === 'PAID' ? 'default' : 'destructive'">
                {{ order.payment_status === 'NOT_PAID' ? "To'lanmagan" : order.payment_status === 'PAID' ? "To'langan" : 'Qaytarilgan' }}
              </Badge>
            </div>
            <div class="flex items-center justify-between pb-3 border-b">
              <span class="text-sm text-muted-foreground">{{ t('orders.amount', 'Summa') }}:</span>
              <span class="font-semibold">{{ formatAmount(order.total_amount) }}</span>
            </div>
            <!-- Payment Link Generator -->
            <div v-if="canGeneratePaymentLink()" class="p-3 bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800">
              <p class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">To'lov havolasini yaratish</p>
              <div v-if="hasConfiguredProvider()" class="flex gap-2 mb-2">
                <Button v-if="paymentProviders?.payme?.configured" size="sm" class="flex-1 bg-[#00CDCD] hover:bg-[#00B5B5]"
                  @click="generatePaymentLink('payme')" :disabled="generatingPaymentLink">{{ generatingPaymentLink ? '...' : 'Payme' }}</Button>
                <Button v-if="paymentProviders?.click?.configured" size="sm" class="flex-1 bg-[#0066FF] hover:bg-[#0052CC]"
                  @click="generatePaymentLink('click')" :disabled="generatingPaymentLink">{{ generatingPaymentLink ? '...' : 'Click' }}</Button>
              </div>
              <p v-else class="text-xs text-orange-600">⚠️ Payme/Click sozlanmagan</p>
              <div v-if="generatedPaymentLink" class="mt-2 space-y-2">
                <div class="flex items-center gap-2"><Input :model-value="generatedPaymentLink" readonly class="text-xs font-mono" />
                  <Button size="sm" variant="secondary" @click="copyPaymentLink">Nusxa</Button></div>
                <p class="text-xs text-green-700">{{ paymentLinkProvider === 'payme' ? 'Payme' : 'Click' }} havolasi tayyor</p>
              </div>
            </div>
            <!-- Payments History -->
            <div v-if="order.payments?.length">
              <p class="text-sm font-medium mb-2">{{ t('payments.history', 'To\'lovlar tarixi') }}</p>
              <div v-for="p in order.payments" :key="p.id" class="p-3 bg-muted rounded mb-2">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-xs text-muted-foreground">{{ p.provider }}</span>
                  <Badge variant="outline" class="text-xs">{{ p.status }}</Badge>
                </div>
                <div class="font-semibold">{{ formatAmount(p.amount, p.currency) }}</div>
                <div class="text-xs text-muted-foreground">ID: {{ p.transaction_id }}</div>
              </div>
            </div>
            <p v-else class="text-sm text-muted-foreground italic">{{ t('payments.noPayments', 'To\'lovlar yo\'q') }}</p>
          </CardContent>
        </Card>

        <!-- Timeline -->
        <Card>
          <CardHeader><CardTitle>{{ t('orders.history', 'Tarix') }}</CardTitle></CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="log in order.logs" :key="log.id" class="flex gap-3">
                <div class="w-2 h-2 mt-2 rounded-full bg-primary flex-shrink-0"></div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium">{{ getLogActionLabel(log.action) }}</p>
                  <p v-if="log.old_value || log.new_value" class="text-xs text-muted-foreground">{{ log.old_value }} → {{ log.new_value }}</p>
                  <p v-if="log.comment" class="text-xs text-muted-foreground mt-1">{{ log.comment }}</p>
                  <p class="text-xs text-muted-foreground mt-1">{{ log.user?.name || 'Tizim' }} - {{ formatDateTime(log.created_at) }}</p>
                </div>
              </div>
              <div class="flex gap-3">
                <div class="w-2 h-2 mt-2 rounded-full bg-green-500 flex-shrink-0"></div>
                <div><p class="text-sm font-medium">{{ t('orders.created', 'Yaratildi') }}</p><p class="text-xs text-muted-foreground">{{ formatDateTime(order.created_at) }}</p></div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Status Modal -->
    <Dialog v-model:open="showStatusModal">
      <DialogContent><DialogHeader><DialogTitle>{{ t('orders.changeStatus', 'Statusni o\'zgartirish') }}</DialogTitle>
        <DialogDescription>{{ t('orders.statusChangeConfirm', 'Yangi status') }}: <strong>{{ getStatusLabel(statusForm.status) }}</strong></DialogDescription></DialogHeader>
        <div class="space-y-4"><Label>{{ t('orders.comment', 'Izoh') }}</Label><Textarea v-model="statusForm.comment" rows="3" /></div>
        <DialogFooter><Button variant="outline" @click="showStatusModal = false">{{ t('common.cancel', 'Bekor') }}</Button>
          <Button @click="submitStatus" :disabled="statusForm.processing">{{ t('common.save', 'Saqlash') }}</Button></DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Reschedule Modal -->
    <Dialog v-model:open="showRescheduleModal">
      <DialogContent><DialogHeader><DialogTitle>{{ t('orders.changeTime', 'Vaqtni o\'zgartirish') }}</DialogTitle></DialogHeader>
        <div class="space-y-4">
          <div class="space-y-2"><Label>{{ t('orders.date', 'Sana') }}</Label><Input type="date" v-model="rescheduleForm.booking_date" /></div>
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2"><Label>Kelish boshlanishi</Label><Input type="time" v-model="rescheduleForm.arrival_window_start" /></div>
            <div class="space-y-2"><Label>Kelish tugashi</Label><Input type="time" v-model="rescheduleForm.arrival_window_end" /></div>
          </div>
          <div class="space-y-2"><Label>{{ t('orders.comment', 'Izoh') }}</Label><Textarea v-model="rescheduleForm.comment" rows="2" /></div>
        </div>
        <DialogFooter><Button variant="outline" @click="showRescheduleModal = false">{{ t('common.cancel', 'Bekor') }}</Button>
          <Button @click="submitReschedule" :disabled="rescheduleForm.processing">{{ t('common.save', 'Saqlash') }}</Button></DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Note Modal -->
    <Dialog v-model:open="showNoteModal">
      <DialogContent><DialogHeader><DialogTitle>{{ t('orders.addNote', 'Izoh qo\'shish') }}</DialogTitle></DialogHeader>
        <Textarea v-model="noteForm.note" rows="4" />
        <DialogFooter><Button variant="outline" @click="showNoteModal = false">{{ t('common.cancel', 'Bekor') }}</Button>
          <Button @click="submitNote" :disabled="noteForm.processing || !noteForm.note">{{ t('common.save', 'Saqlash') }}</Button></DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Cancel Modal -->
    <Dialog v-model:open="showCancelModal">
      <DialogContent><DialogHeader><DialogTitle class="text-destructive">{{ t('orders.cancelOrder', 'Buyurtmani bekor qilish') }}</DialogTitle>
        <DialogDescription>{{ t('orders.cancelConfirm', 'Bu amalni qaytarib bo\'lmaydi.') }}</DialogDescription></DialogHeader>
        <div class="space-y-2"><Label>{{ t('orders.cancelReason', 'Sabab') }} *</Label><Textarea v-model="cancelForm.reason" rows="3" /></div>
        <DialogFooter><Button variant="outline" @click="showCancelModal = false">{{ t('common.cancel', 'Bekor') }}</Button>
          <Button variant="destructive" @click="submitCancel" :disabled="cancelForm.processing || !cancelForm.reason">{{ t('orders.confirmCancel', 'Bekor qilish') }}</Button></DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Confirmation Modal -->
    <Dialog v-model:open="showConfirmationModal">
      <DialogContent class="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader><DialogTitle>Анкета подтверждения</DialogTitle></DialogHeader>
        <div class="space-y-6">
          <div><Label class="mb-2 block">Qo'ng'iroq natijasi *</Label>
            <div class="grid grid-cols-3 gap-2">
              <Button v-for="opt in callOutcomeOptions" :key="opt.value" type="button"
                :variant="confirmationForm.call_outcome === opt.value ? 'default' : 'outline'" size="sm"
                @click="confirmationForm.call_outcome = opt.value">{{ opt.label }}</Button>
            </div></div>
          <Card><CardContent class="pt-4 space-y-4">
            <h4 class="font-medium">Manzil tafsilotlari</h4>
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2"><Label>Kirish (Pod)</Label><Input v-model="confirmationForm.conf_entrance" placeholder="1" /></div>
              <div class="space-y-2"><Label>Qavat</Label><Input v-model="confirmationForm.conf_floor" placeholder="3" /></div>
            </div>
            <div class="flex items-center space-x-2"><Checkbox id="elevator" :checked="confirmationForm.conf_elevator" @update:checked="confirmationForm.conf_elevator = $event" /><Label for="elevator">Lift bor</Label></div>
            <div class="space-y-2"><Label>Parking</Label><Input v-model="confirmationForm.conf_parking" placeholder="Hovlida / Ko'chada" /></div>
            <div class="space-y-2"><Label>Mo'ljal</Label><Input v-model="confirmationForm.conf_landmark" placeholder="Makro yonida, 5-uy" /></div>
          </CardContent></Card>
          <div class="space-y-2"><Label>Joydagi telefon</Label><Input v-model="confirmationForm.conf_onsite_phone" placeholder="+998 90 123 45 67" /></div>
          <Card class="bg-orange-50 dark:bg-orange-950 border-orange-200 dark:border-orange-800"><CardContent class="pt-4 space-y-4">
            <h4 class="font-medium text-orange-800 dark:text-orange-200">Muhim ma'lumotlar</h4>
            <div class="space-y-2"><Label>Cheklovlar (allergia, sog'liq, ...)</Label><Textarea v-model="confirmationForm.conf_constraints" rows="2" /></div>
            <div class="grid grid-cols-2 gap-4">
              <div class="flex items-center space-x-2"><Checkbox id="space" :checked="confirmationForm.conf_space_ok" @update:checked="confirmationForm.conf_space_ok = $event" /><Label for="space">2×2 metr joy bor</Label></div>
              <div class="flex items-center space-x-2"><Checkbox id="pets" :checked="confirmationForm.conf_pets" @update:checked="confirmationForm.conf_pets = $event" /><Label for="pets">Hayvonlar bor</Label></div>
            </div>
          </CardContent></Card>
          <div class="space-y-2"><Label>Ustaga izoh</Label><Textarea v-model="confirmationForm.conf_note_to_master" rows="3" /></div>
        </div>
        <DialogFooter><Button variant="outline" @click="showConfirmationModal = false">Bekor qilish</Button>
          <Button @click="submitConfirmation" :disabled="confirmationForm.processing">Saqlash</Button></DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- QA Modal -->
    <Dialog v-model:open="showQaModal">
      <DialogContent><DialogHeader><DialogTitle>⭐ Sifat nazorati</DialogTitle></DialogHeader>
        <div class="space-y-4">
          <div><Label class="mb-2 block">Umumiy baho</Label><div class="flex gap-1">
            <button v-for="i in 5" :key="i" type="button" @click="qaForm.qa_overall_rating = i" class="text-2xl hover:scale-110 transition" :class="i <= qaForm.qa_overall_rating ? 'text-yellow-400' : 'text-muted'">★</button></div></div>
          <div><Label class="mb-2 block">Vaqtga rioya qilish</Label><div class="flex gap-1">
            <button v-for="i in 5" :key="i" type="button" @click="qaForm.qa_punctuality_rating = i" class="text-2xl hover:scale-110 transition" :class="i <= qaForm.qa_punctuality_rating ? 'text-yellow-400' : 'text-muted'">★</button></div></div>
          <div><Label class="mb-2 block">Professionallik</Label><div class="flex gap-1">
            <button v-for="i in 5" :key="i" type="button" @click="qaForm.qa_professionalism_rating = i" class="text-2xl hover:scale-110 transition" :class="i <= qaForm.qa_professionalism_rating ? 'text-yellow-400' : 'text-muted'">★</button></div></div>
          <div class="space-y-2"><Label>Izoh</Label><Textarea v-model="qaForm.qa_feedback" rows="3" /></div>
        </div>
        <DialogFooter><Button variant="outline" @click="showQaModal = false">Bekor qilish</Button>
          <Button variant="secondary" @click="submitQa" :disabled="qaForm.processing">Saqlash</Button></DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
