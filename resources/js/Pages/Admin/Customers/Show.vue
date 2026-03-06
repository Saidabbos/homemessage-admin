<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
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
  customer: any;
  orders: any;
  ratingsReceived: any[];
  ratingsGiven: any[];
  stats: {
    total_orders: number;
    completed_orders: number;
    cancelled_orders: number;
    total_spent: number;
    avg_rating_received: number | null;
    avg_rating_given: number | null;
  };
}>();

const activeTab = ref('orders');

// Restriction modal
const showRestrictionModal = ref(false);
const restrictionForm = useForm({
  booking_cutoff_hour: '' as string | number,
  restriction_reason: '',
});

// Notes form
const notesForm = useForm({
  admin_notes: props.customer.admin_notes || '',
});
const editingNotes = ref(false);

// Cutoff hour options (8:00 - 23:00)
const cutoffOptions = Array.from({ length: 16 }, (_, i) => ({
  value: i + 8,
  label: `${String(i + 8).padStart(2, '0')}:00`,
}));

const deleteCustomer = () => {
  router.delete(route('admin.customers.destroy', props.customer.id));
};

const saveRestriction = () => {
  restrictionForm.post(route('admin.customers.toggleRestriction', props.customer.id), {
    onSuccess: () => {
      showRestrictionModal.value = false;
      restrictionForm.reset();
    },
  });
};

const removeRestriction = () => {
  router.post(route('admin.customers.toggleRestriction', props.customer.id), {});
};

const saveNotes = () => {
  notesForm.patch(route('admin.customers.updateNotes', props.customer.id), {
    onSuccess: () => {
      editingNotes.value = false;
    },
  });
};

const formatDate = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatShortDate = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  });
};

const formatMoney = (amount: number) => {
  if (!amount) return '0';
  return new Intl.NumberFormat('uz-UZ').format(amount);
};

const getStatusBadgeVariant = (status: string) => {
  const map: Record<string, string> = {
    NEW: 'secondary',
    CONFIRMING: 'outline',
    CONFIRMED: 'default',
    IN_PROGRESS: 'default',
    COMPLETED: 'default',
    CANCELLED: 'destructive',
  };
  return map[status] || 'secondary';
};

const getStatusColor = (status: string) => {
  const map: Record<string, string> = {
    NEW: 'bg-blue-100 text-blue-800',
    CONFIRMING: 'bg-yellow-100 text-yellow-800',
    CONFIRMED: 'bg-indigo-100 text-indigo-800',
    IN_PROGRESS: 'bg-purple-100 text-purple-800',
    COMPLETED: 'bg-green-100 text-green-800',
    CANCELLED: 'bg-red-100 text-red-800',
  };
  return map[status] || 'bg-gray-100 text-gray-800';
};

const renderStars = (rating: number) => {
  return '★'.repeat(Math.round(rating)) + '☆'.repeat(5 - Math.round(rating));
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ customer.name }}</h1>
        <p class="text-muted-foreground">{{ t('customers.info', 'Mijoz ma\'lumotlari') }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.customers.index')">{{ t('common.back', 'Orqaga') }}</Link>
      </Button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold">{{ stats.total_orders }}</div>
          <p class="text-xs text-muted-foreground">{{ t('customers.totalOrders', 'Jami buyurtmalar') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold text-green-600">{{ stats.completed_orders }}</div>
          <p class="text-xs text-muted-foreground">{{ t('customers.completedOrders', 'Bajarilgan') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold text-red-600">{{ stats.cancelled_orders }}</div>
          <p class="text-xs text-muted-foreground">{{ t('customers.cancelledOrders', 'Bekor qilingan') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold">{{ formatMoney(stats.total_spent) }} <span class="text-sm font-normal">UZS</span></div>
          <p class="text-xs text-muted-foreground">{{ t('customers.totalSpent', 'Jami to\'lov') }}</p>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Profile Card -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="flex items-center gap-2">
              <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
              {{ t('customers.personalInfo', 'Shaxsiy ma\'lumotlar') }}
            </CardTitle>
            <div class="flex items-center gap-2">
              <Badge :variant="customer.status ? 'default' : 'destructive'">
                {{ customer.status ? t('common.active', 'Faol') : t('common.inactive', 'Nofaol') }}
              </Badge>
              <Badge v-if="customer.booking_cutoff_hour !== null" variant="destructive" class="bg-orange-600">
                {{ String(customer.booking_cutoff_hour).padStart(2, '0') }}:00 {{ t('customers.cutoffAfter', 'dan keyin cheklangan') }}
              </Badge>
            </div>
          </CardHeader>
          <CardContent>
            <div class="flex items-center mb-6">
              <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-2xl font-semibold">
                {{ customer.name?.charAt(0).toUpperCase() }}
              </div>
              <div class="ml-4">
                <h4 class="text-lg font-semibold">{{ customer.name }}</h4>
                <p class="text-sm text-muted-foreground">{{ t('customers.singular', 'Mijoz') }}</p>
                <div v-if="stats.avg_rating_received" class="flex items-center gap-1 mt-1">
                  <span class="text-yellow-500 text-sm">{{ renderStars(stats.avg_rating_received) }}</span>
                  <span class="text-xs text-muted-foreground">({{ stats.avg_rating_received?.toFixed(1) }})</span>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('customers.email', 'Email') }}:</span>
                <a v-if="customer.email" :href="`mailto:${customer.email}`" class="text-primary hover:underline font-medium">{{ customer.email }}</a>
                <span v-else class="text-muted-foreground">-</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('customers.phone', 'Telefon') }}:</span>
                <span v-if="customer.phone" class="font-medium">
                  <a :href="`tel:${customer.phone}`" class="text-primary hover:underline">{{ customer.phone }}</a>
                </span>
                <span v-else class="text-muted-foreground">-</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('customers.gender', 'Jinsi') }}:</span>
                <span class="font-medium">{{ customer.gender || '-' }}</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('common.id', 'ID') }}:</span>
                <span class="font-mono font-medium">#{{ customer.id }}</span>
              </div>
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('common.createdAt', 'Yaratilgan') }}:</span>
                <span class="font-medium">{{ formatDate(customer.created_at) }}</span>
              </div>
              <div class="flex items-center justify-between py-2">
                <span class="text-muted-foreground">{{ t('common.updatedAt', 'Yangilangan') }}:</span>
                <span class="font-medium">{{ formatDate(customer.updated_at) }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Tabs -->
        <div class="flex border-b">
          <button
            v-for="tab in ['orders', 'ratingsReceived', 'ratingsGiven']"
            :key="tab"
            @click="activeTab = tab"
            class="px-4 py-2 text-sm font-medium border-b-2 transition-colors"
            :class="activeTab === tab ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground'"
          >
            <template v-if="tab === 'orders'">{{ t('customers.orderHistory', 'Buyurtmalar tarixi') }} ({{ stats.total_orders }})</template>
            <template v-if="tab === 'ratingsReceived'">{{ t('customers.ratingsReceived', 'Olingan baholar') }} ({{ ratingsReceived.length }})</template>
            <template v-if="tab === 'ratingsGiven'">{{ t('customers.ratingsGiven', 'Bergan baholar') }} ({{ ratingsGiven.length }})</template>
          </button>
        </div>

        <!-- Orders Tab -->
        <Card v-if="activeTab === 'orders'">
          <CardContent class="pt-6">
            <div v-if="orders.data.length === 0" class="text-center py-8 text-muted-foreground">
              {{ t('customers.noOrders', 'Buyurtmalar topilmadi') }}
            </div>
            <div v-else class="space-y-3">
              <div
                v-for="order in orders.data"
                :key="order.id"
                class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors"
              >
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <Link :href="route('admin.orders.show', order.id)" class="font-mono text-sm font-medium text-primary hover:underline">
                      {{ order.order_number }}
                    </Link>
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.status)]">
                      {{ order.status }}
                    </span>
                  </div>
                  <div class="flex items-center gap-3 mt-1 text-sm text-muted-foreground">
                    <span v-if="order.service_type">{{ order.service_type.name?.uz || order.service_type.name }}</span>
                    <span v-if="order.master">
                      <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                      {{ order.master.first_name }} {{ order.master.last_name }}
                    </span>
                    <span>{{ formatShortDate(order.booking_date) }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-semibold">{{ formatMoney(order.total_amount) }} UZS</div>
                  <div class="text-xs" :class="order.payment_status === 'PAID' ? 'text-green-600' : 'text-muted-foreground'">
                    {{ order.payment_status }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Pagination -->
            <div v-if="orders.last_page > 1" class="flex justify-center gap-1 mt-4 pt-4 border-t">
              <Link
                v-for="link in orders.links"
                :key="link.label"
                :href="link.url || '#'"
                class="px-3 py-1 text-sm rounded border"
                :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'"
                v-html="link.label"
              />
            </div>
          </CardContent>
        </Card>

        <!-- Ratings Received Tab -->
        <Card v-if="activeTab === 'ratingsReceived'">
          <CardContent class="pt-6">
            <div v-if="ratingsReceived.length === 0" class="text-center py-8 text-muted-foreground">
              {{ t('customers.noRatings', 'Baholar topilmadi') }}
            </div>
            <div v-else class="space-y-3">
              <div v-for="rating in ratingsReceived" :key="rating.id" class="p-3 rounded-lg border">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="text-yellow-500">{{ renderStars(rating.overall_rating) }}</span>
                    <span class="text-sm font-medium">{{ rating.overall_rating }}/5</span>
                  </div>
                  <span class="text-xs text-muted-foreground">{{ formatShortDate(rating.rated_at) }}</span>
                </div>
                <div class="mt-2 flex items-center gap-3 text-sm text-muted-foreground">
                  <span v-if="rating.master">
                    {{ t('customers.fromMaster', 'Masterdan') }}: {{ rating.master.first_name }} {{ rating.master.last_name }}
                  </span>
                  <Link v-if="rating.order" :href="route('admin.orders.show', rating.order.id)" class="text-primary hover:underline">
                    {{ rating.order.order_number }}
                  </Link>
                </div>
                <div v-if="rating.punctuality_rating || rating.professionalism_rating || rating.cleanliness_rating" class="mt-2 flex gap-4 text-xs text-muted-foreground">
                  <span v-if="rating.punctuality_rating">{{ t('customers.punctuality', 'Vaqtida') }}: {{ rating.punctuality_rating }}/5</span>
                  <span v-if="rating.professionalism_rating">{{ t('customers.professionalism', 'Professional') }}: {{ rating.professionalism_rating }}/5</span>
                  <span v-if="rating.cleanliness_rating">{{ t('customers.cleanliness', 'Tozalik') }}: {{ rating.cleanliness_rating }}/5</span>
                </div>
                <p v-if="rating.feedback" class="mt-2 text-sm italic text-muted-foreground">"{{ rating.feedback }}"</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Ratings Given Tab -->
        <Card v-if="activeTab === 'ratingsGiven'">
          <CardContent class="pt-6">
            <div v-if="ratingsGiven.length === 0" class="text-center py-8 text-muted-foreground">
              {{ t('customers.noRatings', 'Baholar topilmadi') }}
            </div>
            <div v-else class="space-y-3">
              <div v-for="rating in ratingsGiven" :key="rating.id" class="p-3 rounded-lg border">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="text-yellow-500">{{ renderStars(rating.overall_rating) }}</span>
                    <span class="text-sm font-medium">{{ rating.overall_rating }}/5</span>
                  </div>
                  <span class="text-xs text-muted-foreground">{{ formatShortDate(rating.rated_at) }}</span>
                </div>
                <div class="mt-2 flex items-center gap-3 text-sm text-muted-foreground">
                  <span v-if="rating.master">
                    {{ t('customers.toMaster', 'Masterga') }}: {{ rating.master.first_name }} {{ rating.master.last_name }}
                  </span>
                  <Link v-if="rating.order" :href="route('admin.orders.show', rating.order.id)" class="text-primary hover:underline">
                    {{ rating.order.order_number }}
                  </Link>
                </div>
                <div v-if="rating.punctuality_rating || rating.professionalism_rating || rating.cleanliness_rating" class="mt-2 flex gap-4 text-xs text-muted-foreground">
                  <span v-if="rating.punctuality_rating">{{ t('customers.punctuality', 'Vaqtida') }}: {{ rating.punctuality_rating }}/5</span>
                  <span v-if="rating.professionalism_rating">{{ t('customers.professionalism', 'Professional') }}: {{ rating.professionalism_rating }}/5</span>
                  <span v-if="rating.cleanliness_rating">{{ t('customers.cleanliness', 'Tozalik') }}: {{ rating.cleanliness_rating }}/5</span>
                </div>
                <p v-if="rating.feedback" class="mt-2 text-sm italic text-muted-foreground">"{{ rating.feedback }}"</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Actions -->
        <Card>
          <CardHeader>
            <CardTitle class="text-base">{{ t('common.actions', 'Amallar') }}</CardTitle>
          </CardHeader>
          <CardContent class="space-y-2">
            <Button class="w-full" as-child>
              <Link :href="route('admin.customers.edit', customer.id)">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ t('common.edit', 'Tahrirlash') }}
              </Link>
            </Button>

            <Button variant="outline" class="w-full" as-child>
              <Link :href="route('admin.customers.index')">{{ t('common.back', 'Orqaga') }}</Link>
            </Button>

            <Separator />

            <AlertDialog>
              <AlertDialogTrigger as-child>
                <Button variant="destructive" class="w-full">
                  <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                  {{ t('common.delete', 'O\'chirish') }}
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle>{{ t('common.confirmDelete', 'O\'chirishni tasdiqlang') }}</AlertDialogTitle>
                  <AlertDialogDescription>
                    {{ t('customers.confirmDelete', 'Bu mijozni o\'chirishni xohlaysizmi?') }}
                    <strong class="block mt-2">{{ customer.name }}</strong>
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel>{{ t('common.cancel', 'Bekor') }}</AlertDialogCancel>
                  <AlertDialogAction @click="deleteCustomer" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                    {{ t('common.delete', 'O\'chirish') }}
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </CardContent>
        </Card>

        <!-- Slot Time Restriction Card -->
        <Card :class="customer.booking_cutoff_hour !== null ? 'border-orange-300 bg-orange-50/50' : ''">
          <CardHeader>
            <CardTitle class="text-base flex items-center gap-2">
              <svg class="w-5 h-5" :class="customer.booking_cutoff_hour !== null ? 'text-orange-600' : 'text-muted-foreground'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ t('customers.slotRestriction', 'Vaqt slot cheklovi') }}
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="customer.booking_cutoff_hour !== null" class="space-y-3">
              <div class="p-3 rounded-lg bg-orange-100 text-orange-800 text-sm">
                <div class="font-semibold flex items-center gap-2">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  {{ t('customers.cutoffAfter', 'dan keyin cheklangan') }}: {{ String(customer.booking_cutoff_hour).padStart(2, '0') }}:00
                </div>
                <div class="mt-1">
                  {{ t('customers.cutoffDesc', 'Bu mijoz soat') }} {{ String(customer.booking_cutoff_hour).padStart(2, '0') }}:00 {{ t('customers.cutoffDescAfter', 'dan keyin buyurtma bera olmaydi') }}
                </div>
                <div v-if="customer.restriction_reason" class="mt-1">
                  {{ t('customers.reason', 'Sabab') }}: {{ customer.restriction_reason }}
                </div>
                <div v-if="customer.restricted_by_user" class="mt-1 text-xs">
                  {{ t('customers.restrictedBy', 'Cheklagan') }}: {{ customer.restricted_by_user.name }}
                  <span v-if="customer.restricted_at"> &middot; {{ formatDate(customer.restricted_at) }}</span>
                </div>
              </div>
              <Button variant="outline" class="w-full" @click="removeRestriction">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ t('customers.removeRestriction', 'Cheklovni olib tashlash') }}
              </Button>
            </div>
            <div v-else>
              <p class="text-sm text-muted-foreground mb-3">{{ t('customers.noSlotRestriction', 'Vaqt cheklovi qo\'yilmagan') }}</p>
              <Button variant="outline" class="w-full text-orange-600 border-orange-300 hover:bg-orange-50" @click="showRestrictionModal = true">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ t('customers.addSlotRestriction', 'Vaqt cheklovi qo\'yish') }}
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Admin Notes Card -->
        <Card>
          <CardHeader class="flex flex-row items-center justify-between">
            <CardTitle class="text-base">{{ t('customers.adminNotes', 'Admin izohlari') }}</CardTitle>
            <Button v-if="!editingNotes" variant="ghost" size="sm" @click="editingNotes = true">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
            </Button>
          </CardHeader>
          <CardContent>
            <div v-if="editingNotes">
              <Textarea
                v-model="notesForm.admin_notes"
                :placeholder="t('customers.enterNotes', 'Izoh yozing...')"
                rows="4"
              />
              <div class="flex gap-2 mt-2">
                <Button size="sm" @click="saveNotes" :disabled="notesForm.processing">
                  {{ t('common.save', 'Saqlash') }}
                </Button>
                <Button size="sm" variant="outline" @click="editingNotes = false; notesForm.admin_notes = customer.admin_notes || ''">
                  {{ t('common.cancel', 'Bekor') }}
                </Button>
              </div>
            </div>
            <div v-else>
              <p v-if="customer.admin_notes" class="text-sm whitespace-pre-wrap">{{ customer.admin_notes }}</p>
              <p v-else class="text-sm text-muted-foreground italic">{{ t('customers.noNotes', 'Izoh yo\'q') }}</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Slot Restriction Modal -->
    <Dialog v-model:open="showRestrictionModal">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ t('customers.addSlotRestriction', 'Vaqt cheklovi qo\'yish') }}</DialogTitle>
          <DialogDescription>
            {{ t('customers.slotRestrictionDesc', 'Tanlangan soatdan keyin bu mijoz slot tanlashi cheklanadi') }}
          </DialogDescription>
        </DialogHeader>
        <div class="space-y-4">
          <div>
            <label class="text-sm font-medium">{{ t('customers.cutoffHour', 'Cheklash soati') }}</label>
            <select v-model="restrictionForm.booking_cutoff_hour" class="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
              <option value="" disabled>{{ t('customers.selectCutoffHour', 'Soatni tanlang') }}</option>
              <option v-for="opt in cutoffOptions" :key="opt.value" :value="opt.value">
                {{ opt.label }} {{ t('customers.andAfter', 'dan keyin') }}
              </option>
            </select>
            <p class="text-xs text-muted-foreground mt-1">
              {{ t('customers.cutoffHint', 'Mijoz bu soatdan keyin slot tanlolmaydi') }}
            </p>
          </div>
          <div>
            <label class="text-sm font-medium">{{ t('customers.reason', 'Sabab') }}</label>
            <Input v-model="restrictionForm.restriction_reason" :placeholder="t('customers.enterReason', 'Sababni kiriting')" class="mt-1" />
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="showRestrictionModal = false">{{ t('common.cancel', 'Bekor') }}</Button>
          <Button
            variant="destructive"
            @click="saveRestriction"
            :disabled="restrictionForm.processing || !restrictionForm.booking_cutoff_hour"
            class="bg-orange-600 hover:bg-orange-700"
          >
            {{ t('customers.confirm', 'Tasdiqlash') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>
