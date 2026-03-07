<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';

defineOptions({ layout: AdminLayout });

const { t, locale } = useI18n();

const props = defineProps<{
  master: any;
  orders: any;
  ratingsReceived: any[];
  ratingsGiven: any[];
  stats: {
    total_orders: number;
    completed_orders: number;
    cancelled_orders: number;
    total_earned: number;
    avg_rating: number | null;
    ratings_count: number;
  };
}>();

const activeTab = ref('orders');
const bioTab = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek" },
  { key: 'ru', label: 'Русский' },
  { key: 'en', label: 'English' },
];

const getTranslation = (item: any, field: string) => {
  if (!item[field]) return '';
  if (typeof item[field] === 'string') return item[field];
  return item[field][locale.value] || item[field]['uz'] || Object.values(item[field])[0] || '';
};

const formatDate = (date: string) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('uz-UZ', {
    year: 'numeric', month: '2-digit', day: '2-digit',
  });
};

const formatMoney = (amount: number) => {
  if (!amount) return '0';
  return new Intl.NumberFormat('uz-UZ').format(amount);
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
        <h1 class="text-2xl font-bold tracking-tight">{{ master.full_name }}</h1>
        <p class="text-muted-foreground">{{ t('masters.info', 'Master ma\'lumotlari') }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.masters.index')">{{ t('common.back', 'Orqaga') }}</Link>
      </Button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold">{{ stats.total_orders }}</div>
          <p class="text-xs text-muted-foreground">{{ t('masters.totalOrders', 'Jami buyurtmalar') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold text-green-600">{{ stats.completed_orders }}</div>
          <p class="text-xs text-muted-foreground">{{ t('masters.completedOrders', 'Bajarilgan') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold text-red-600">{{ stats.cancelled_orders }}</div>
          <p class="text-xs text-muted-foreground">{{ t('masters.cancelledOrders', 'Bekor qilingan') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold">{{ formatMoney(stats.total_earned) }} <span class="text-sm font-normal">UZS</span></div>
          <p class="text-xs text-muted-foreground">{{ t('masters.totalEarned', 'Jami daromad') }}</p>
        </CardContent>
      </Card>
      <Card>
        <CardContent class="pt-6">
          <div class="text-2xl font-bold text-yellow-500 flex items-center gap-1">
            <span v-if="stats.avg_rating">{{ stats.avg_rating.toFixed(1) }}</span>
            <span v-else>-</span>
            <span class="text-base">★</span>
          </div>
          <p class="text-xs text-muted-foreground">{{ t('masters.avgRating', 'O\'rtacha baho') }} ({{ stats.ratings_count }})</p>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Profile Card -->
      <div class="lg:col-span-1 space-y-6">
        <Card>
          <CardContent class="pt-6">
            <div class="text-center">
              <img :src="master.photo_url" :alt="master.full_name" class="w-32 h-32 rounded-full mx-auto border-4 border-muted object-cover" />
              <h3 class="mt-4 text-xl font-semibold">{{ master.full_name }}</h3>
              <p class="text-muted-foreground">{{ master.gender === 'male' ? t('masters.male', 'Erkak') : t('masters.female', 'Ayol') }}</p>
              <div class="mt-2 flex items-center justify-center gap-2">
                <Badge :variant="master.status ? 'default' : 'destructive'">
                  {{ master.status ? t('common.active', 'Faol') : t('common.inactive', 'Nofaol') }}
                </Badge>
                <div v-if="stats.avg_rating" class="flex items-center gap-1">
                  <span class="text-yellow-500 text-sm">{{ renderStars(stats.avg_rating) }}</span>
                </div>
              </div>
            </div>

            <Separator class="my-6" />

            <!-- Contact Info -->
            <div class="space-y-3">
              <div class="flex items-center justify-between py-1">
                <span class="text-sm text-muted-foreground">{{ t('masters.phone', 'Telefon') }}</span>
                <a :href="'tel:' + master.phone" class="text-sm text-primary hover:underline font-medium">{{ master.phone }}</a>
              </div>
              <div class="flex items-center justify-between py-1">
                <span class="text-sm text-muted-foreground">{{ t('masters.email', 'Email') }}</span>
                <a v-if="master.email" :href="'mailto:' + master.email" class="text-sm text-primary hover:underline font-medium">{{ master.email }}</a>
                <span v-else class="text-sm text-muted-foreground">-</span>
              </div>
              <div class="flex items-center justify-between py-1">
                <span class="text-sm text-muted-foreground">{{ t('masters.experience', 'Tajriba') }}</span>
                <span class="text-sm font-medium">{{ master.experience_years }} {{ t('masters.years', 'yil') }}</span>
              </div>
              <div class="flex items-center justify-between py-1">
                <span class="text-sm text-muted-foreground">{{ t('masters.birthDate', 'Tug\'ilgan sana') }}</span>
                <span class="text-sm font-medium">{{ master.birth_date ? formatDate(master.birth_date) : '-' }}</span>
              </div>
              <div class="flex items-center justify-between py-1">
                <span class="text-sm text-muted-foreground">{{ t('masters.shift', 'Smena') }}</span>
                <span class="text-sm font-medium">{{ master.shift_start }} – {{ master.shift_end }}</span>
              </div>
            </div>

            <Separator class="my-6" />

            <div class="space-y-2">
              <Button class="w-full" as-child>
                <Link :href="route('admin.masters.edit', master.id)">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                  {{ t('common.edit', 'Tahrirlash') }}
                </Link>
              </Button>
              <Button variant="outline" class="w-full" as-child>
                <Link :href="route('admin.masters.schedule', master.id)">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  {{ t('masters.schedule', 'Jadval') }}
                </Link>
              </Button>
              <Button variant="outline" class="w-full" as-child>
                <Link :href="route('admin.masters.index')">{{ t('common.back', 'Orqaga') }}</Link>
              </Button>
            </div>
          </CardContent>
        </Card>

        <!-- Services & Oils -->
        <Card>
          <CardHeader><CardTitle class="text-base">{{ t('masters.services', 'Xizmatlar') }}</CardTitle></CardHeader>
          <CardContent>
            <div v-if="master.service_types?.length" class="flex flex-wrap gap-2">
              <Badge v-for="s in master.service_types" :key="s.id" variant="secondary">{{ getTranslation(s, 'name') }}</Badge>
            </div>
            <p v-else class="text-sm text-muted-foreground">{{ t('masters.noServices', 'Xizmatlar yo\'q') }}</p>

            <Separator class="my-4" />
            <p class="text-sm font-medium mb-2">{{ t('masters.oils', 'Yog\'lar') }}</p>
            <div v-if="master.oils?.length" class="flex flex-wrap gap-2">
              <Badge v-for="o in master.oils" :key="o.id" variant="outline" class="bg-green-50 text-green-700 border-green-200">{{ getTranslation(o, 'name') }}</Badge>
            </div>
            <p v-else class="text-sm text-muted-foreground">{{ t('masters.noOils', 'Yog\'lar yo\'q') }}</p>

            <template v-if="master.pressure_levels?.length">
              <Separator class="my-4" />
              <p class="text-sm font-medium mb-2">{{ t('masters.pressureLevels', 'Bosim darajalari') }}</p>
              <div class="flex flex-wrap gap-2">
                <Badge v-for="p in master.pressure_levels" :key="p.id" variant="outline" class="bg-blue-50 text-blue-700 border-blue-200">{{ getTranslation(p, 'name') }}</Badge>
              </div>
            </template>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Bio -->
        <Card>
          <CardHeader><CardTitle>{{ t('masters.bio', 'Biografiya') }}</CardTitle></CardHeader>
          <CardContent>
            <div class="flex gap-2 mb-4">
              <Button v-for="tab in languageTabs" :key="tab.key" type="button" :variant="bioTab === tab.key ? 'default' : 'outline'" size="sm" @click="bioTab = tab.key">{{ tab.label }}</Button>
            </div>
            <div v-for="tab in languageTabs" :key="tab.key" v-show="bioTab === tab.key">
              <p v-if="master.bio?.[tab.key]" class="whitespace-pre-line">{{ master.bio[tab.key] }}</p>
              <p v-else class="text-muted-foreground italic">{{ t('translations.noDescription', 'Tavsif yo\'q') }}</p>
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
            <template v-if="tab === 'orders'">{{ t('masters.orderHistory', 'Buyurtmalar tarixi') }} ({{ stats.total_orders }})</template>
            <template v-if="tab === 'ratingsReceived'">{{ t('masters.ratingsFromClients', 'Mijozlardan baholar') }} ({{ ratingsReceived.length }})</template>
            <template v-if="tab === 'ratingsGiven'">{{ t('masters.ratingsToClients', 'Mijozlarga baholar') }} ({{ ratingsGiven.length }})</template>
          </button>
        </div>

        <!-- Orders Tab -->
        <Card v-if="activeTab === 'orders'">
          <CardContent class="pt-6">
            <div v-if="orders.data.length === 0" class="text-center py-8 text-muted-foreground">
              {{ t('masters.noOrders', 'Buyurtmalar topilmadi') }}
            </div>
            <div v-else class="space-y-3">
              <div v-for="order in orders.data" :key="order.id" class="flex items-center justify-between p-3 rounded-lg border hover:bg-muted/50 transition-colors">
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2">
                    <Link :href="route('admin.orders.show', order.id)" class="font-mono text-sm font-medium text-primary hover:underline">{{ order.order_number }}</Link>
                    <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', getStatusColor(order.status)]">{{ order.status }}</span>
                  </div>
                  <div class="flex items-center gap-3 mt-1 text-sm text-muted-foreground">
                    <span v-if="order.service_type">{{ getTranslation(order.service_type, 'name') }}</span>
                    <span v-if="order.customer">
                      <svg class="w-3 h-3 inline mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                      {{ order.customer.name }}
                    </span>
                    <span>{{ formatDate(order.booking_date) }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-semibold">{{ formatMoney(order.total_amount) }} UZS</div>
                  <div class="text-xs" :class="order.payment_status === 'PAID' ? 'text-green-600' : 'text-muted-foreground'">{{ order.payment_status }}</div>
                </div>
              </div>
            </div>
            <div v-if="orders.last_page > 1" class="flex justify-center gap-1 mt-4 pt-4 border-t">
              <Link v-for="link in orders.links" :key="link.label" :href="link.url || '#'" class="px-3 py-1 text-sm rounded border" :class="link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'" v-html="link.label" />
            </div>
          </CardContent>
        </Card>

        <!-- Ratings Received Tab -->
        <Card v-if="activeTab === 'ratingsReceived'">
          <CardContent class="pt-6">
            <div v-if="ratingsReceived.length === 0" class="text-center py-8 text-muted-foreground">
              {{ t('masters.noRatings', 'Baholar topilmadi') }}
            </div>
            <div v-else class="space-y-3">
              <div v-for="rating in ratingsReceived" :key="rating.id" class="p-3 rounded-lg border">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="text-yellow-500">{{ renderStars(rating.overall_rating) }}</span>
                    <span class="text-sm font-medium">{{ rating.overall_rating }}/5</span>
                  </div>
                  <span class="text-xs text-muted-foreground">{{ formatDate(rating.rated_at) }}</span>
                </div>
                <div class="mt-2 flex items-center gap-3 text-sm text-muted-foreground">
                  <span v-if="rating.customer">
                    {{ t('masters.fromClient', 'Mijozdan') }}: {{ rating.customer.name }}
                  </span>
                  <Link v-if="rating.order" :href="route('admin.orders.show', rating.order.id)" class="text-primary hover:underline">{{ rating.order.order_number }}</Link>
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
              {{ t('masters.noRatings', 'Baholar topilmadi') }}
            </div>
            <div v-else class="space-y-3">
              <div v-for="rating in ratingsGiven" :key="rating.id" class="p-3 rounded-lg border">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="text-yellow-500">{{ renderStars(rating.overall_rating) }}</span>
                    <span class="text-sm font-medium">{{ rating.overall_rating }}/5</span>
                  </div>
                  <span class="text-xs text-muted-foreground">{{ formatDate(rating.rated_at) }}</span>
                </div>
                <div class="mt-2 flex items-center gap-3 text-sm text-muted-foreground">
                  <span v-if="rating.customer">
                    {{ t('masters.toClient', 'Mijozga') }}: {{ rating.customer.name }}
                  </span>
                  <Link v-if="rating.order" :href="route('admin.orders.show', rating.order.id)" class="text-primary hover:underline">{{ rating.order.order_number }}</Link>
                </div>
                <p v-if="rating.feedback" class="mt-2 text-sm italic text-muted-foreground">"{{ rating.feedback }}"</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
