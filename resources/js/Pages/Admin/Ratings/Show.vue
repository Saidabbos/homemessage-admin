<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Separator } from '@/components/ui/separator';
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

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
  rating: any;
}>();

const deleteRating = () => {
  router.delete(route('admin.ratings.destroy', props.rating.id));
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('ratings.details', 'Baho tafsilotlari') }}</h1>
        <p class="text-muted-foreground">{{ rating.order?.order_number || 'Buyurtma yo\'q' }}</p>
      </div>
      <div class="flex gap-2">
        <Button variant="outline" as-child>
          <Link :href="route('admin.ratings.index')">{{ t('common.back', 'Orqaga') }}</Link>
        </Button>
        <AlertDialog>
          <AlertDialogTrigger as-child>
            <Button variant="destructive">
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
                Haqiqatan ham bu bahoni o'chirmoqchimisiz?
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel>{{ t('common.cancel', 'Bekor') }}</AlertDialogCancel>
              <AlertDialogAction @click="deleteRating" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
                {{ t('common.delete', 'O\'chirish') }}
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Rating Info -->
      <Card>
        <CardHeader>
          <CardTitle>{{ t('ratings.info', 'Baho ma\'lumotlari') }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-6">
          <!-- Type Badge -->
          <div>
            <Badge :variant="rating.type === 'client_to_master' ? 'default' : 'secondary'">
              {{ rating.type === 'client_to_master' ? 'Mijoz → Master' : 'Master → Mijoz' }}
            </Badge>
          </div>

          <!-- Overall Rating -->
          <div class="text-center py-6 bg-muted/50 rounded-xl">
            <div v-if="rating.overall_rating" class="text-5xl font-bold text-yellow-500 mb-2">
              {{ rating.overall_rating }}
            </div>
            <div v-else class="text-2xl text-muted-foreground mb-2">Kutilmoqda</div>
            <div class="flex justify-center">
              <svg v-for="i in 5" :key="i" class="w-8 h-8" :class="i <= rating.overall_rating ? 'text-yellow-400' : 'text-muted'" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
            </div>
          </div>

          <!-- Detailed Ratings -->
          <div v-if="rating.overall_rating" class="space-y-3">
            <div v-if="rating.punctuality_rating" class="flex items-center justify-between">
              <span class="text-muted-foreground">Vaqtida kelish</span>
              <div class="flex">
                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.punctuality_rating ? 'text-yellow-400' : 'text-muted'" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
            </div>
            <div v-if="rating.professionalism_rating" class="flex items-center justify-between">
              <span class="text-muted-foreground">Professionallik</span>
              <div class="flex">
                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.professionalism_rating ? 'text-yellow-400' : 'text-muted'" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
            </div>
            <div v-if="rating.cleanliness_rating" class="flex items-center justify-between">
              <span class="text-muted-foreground">Tozalik</span>
              <div class="flex">
                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= rating.cleanliness_rating ? 'text-yellow-400' : 'text-muted'" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
              </div>
            </div>
          </div>

          <!-- Feedback -->
          <div v-if="rating.feedback" class="p-4 bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Izoh</h3>
            <p class="text-blue-700 dark:text-blue-300">"{{ rating.feedback }}"</p>
          </div>

          <!-- Timestamps -->
          <Separator />
          <div class="text-sm text-muted-foreground space-y-1">
            <div>Yaratilgan: {{ rating.created_at }}</div>
            <div v-if="rating.rated_at">Baholangan: {{ rating.rated_at }}</div>
          </div>
        </CardContent>
      </Card>

      <!-- Participants -->
      <div class="space-y-6">
        <!-- Master Card -->
        <Card v-if="rating.master">
          <CardHeader>
            <CardTitle>Master</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center">
              <img v-if="rating.master.photo" :src="rating.master.photo" class="w-16 h-16 rounded-full object-cover" />
              <div v-else class="w-16 h-16 rounded-full bg-muted flex items-center justify-center text-xl text-muted-foreground">
                {{ rating.master.name?.charAt(0) }}
              </div>
              <div class="ml-4">
                <h3 class="font-semibold">{{ rating.master.name }}</h3>
                <div v-if="rating.master.rating" class="flex items-center text-sm text-muted-foreground">
                  <svg class="w-4 h-4 text-yellow-400 mr-1" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                  </svg>
                  {{ parseFloat(rating.master.rating).toFixed(1) }} ({{ rating.master.rating_count }} ta baho)
                </div>
              </div>
            </div>
            <Button variant="secondary" class="w-full mt-4" size="sm" as-child>
              <Link :href="`/admin/masters/${rating.master.id}`">Masterni ko'rish</Link>
            </Button>
          </CardContent>
        </Card>

        <!-- Customer Card -->
        <Card v-if="rating.customer">
          <CardHeader>
            <CardTitle>Mijoz</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex items-center">
              <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center text-xl text-primary">
                {{ rating.customer.name?.charAt(0) }}
              </div>
              <div class="ml-4">
                <h3 class="font-semibold">{{ rating.customer.name }}</h3>
                <p class="text-sm text-muted-foreground">{{ rating.customer.phone }}</p>
              </div>
            </div>
            <Button variant="secondary" class="w-full mt-4" size="sm" as-child>
              <Link :href="`/admin/customers/${rating.customer.id}`">Mijozni ko'rish</Link>
            </Button>
          </CardContent>
        </Card>

        <!-- Order Card -->
        <Card v-if="rating.order">
          <CardHeader>
            <CardTitle>Buyurtma</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Raqam:</span>
                <span class="font-mono">{{ rating.order.order_number }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Xizmat:</span>
                <span>{{ rating.order.service }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Sana:</span>
                <span>{{ rating.order.booking_date }}</span>
              </div>
            </div>
            <Button class="w-full mt-4" size="sm" as-child>
              <Link :href="`/admin/orders/${rating.order.id}`">Buyurtmani ko'rish</Link>
            </Button>
          </CardContent>
        </Card>
      </div>
    </div>
  </div>
</template>
