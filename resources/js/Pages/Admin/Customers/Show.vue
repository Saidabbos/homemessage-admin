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
  customer: any;
}>();

const deleteCustomer = () => {
  router.delete(route('admin.customers.destroy', props.customer.id));
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
            <Badge :variant="customer.status ? 'default' : 'destructive'">
              {{ customer.status ? t('common.active', 'Faol') : t('common.inactive', 'Nofaol') }}
            </Badge>
          </CardHeader>
          <CardContent>
            <div class="flex items-center mb-6">
              <div class="w-16 h-16 rounded-full bg-primary flex items-center justify-center text-primary-foreground text-2xl font-semibold">
                {{ customer.name.charAt(0).toUpperCase() }}
              </div>
              <div class="ml-4">
                <h4 class="text-lg font-semibold">{{ customer.name }}</h4>
                <p class="text-sm text-muted-foreground">{{ t('customers.singular', 'Mijoz') }}</p>
              </div>
            </div>

            <div class="space-y-3">
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('customers.email', 'Email') }}:</span>
                <a :href="`mailto:${customer.email}`" class="text-primary hover:underline font-medium">{{ customer.email }}</a>
              </div>
              <div class="flex items-center justify-between py-2 border-b">
                <span class="text-muted-foreground">{{ t('customers.phone', 'Telefon') }}:</span>
                <span v-if="customer.phone" class="font-medium">
                  <a :href="`tel:${customer.phone}`" class="text-primary hover:underline">{{ customer.phone }}</a>
                </span>
                <span v-else class="text-muted-foreground">-</span>
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
      </div>
    </div>
  </div>
</template>
