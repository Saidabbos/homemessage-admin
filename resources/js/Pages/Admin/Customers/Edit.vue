<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
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

const form = useForm({
  name: props.customer.name,
  email: props.customer.email,
  phone: props.customer.phone || '',
  status: props.customer.status,
});

const submit = () => {
  form.patch(route('admin.customers.update', props.customer.id));
};

const deleteCustomer = () => {
  router.delete(route('admin.customers.destroy', props.customer.id));
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('uz-UZ');
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('common.edit', 'Tahrirlash') }}</h1>
        <p class="text-muted-foreground">{{ customer.name }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.customers.index')">{{ t('common.back', 'Orqaga') }}</Link>
      </Button>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Personal Info -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                {{ t('customers.personalInfo', 'Shaxsiy ma\'lumotlar') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <Label>{{ t('customers.name', 'Ism') }} <span class="text-destructive">*</span></Label>
                <Input v-model="form.name" placeholder="Ism Familiya" />
                <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
              </div>

              <div class="space-y-2">
                <Label>{{ t('customers.email', 'Email') }}</Label>
                <Input type="email" v-model="form.email" placeholder="email@example.com" />
                <p v-if="form.errors.email" class="text-destructive text-xs">{{ form.errors.email }}</p>
              </div>

              <div class="space-y-2">
                <Label>{{ t('customers.phone', 'Telefon') }}</Label>
                <Input v-model="form.phone" placeholder="+998 90 123 45 67" />
                <p v-if="form.errors.phone" class="text-destructive text-xs">{{ form.errors.phone }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Status -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.status', 'Status') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-between">
                <div>
                  <Label>{{ t('common.active', 'Faol') }}</Label>
                  <p class="text-xs text-muted-foreground">{{ form.status ? 'Mijoz faol' : 'Bloklangan' }}</p>
                </div>
                <Switch v-model:checked="form.status" />
              </div>
            </CardContent>
          </Card>

          <!-- Meta Info -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.info', 'Ma\'lumot') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-muted-foreground">ID:</span>
                <span class="font-mono">#{{ customer.id }}</span>
              </div>
              <Separator />
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.createdAt', 'Yaratilgan') }}:</span>
                <span>{{ formatDate(customer.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.updatedAt', 'Yangilangan') }}:</span>
                <span>{{ formatDate(customer.updated_at) }}</span>
              </div>
            </CardContent>
          </Card>

          <!-- Actions -->
          <Card>
            <CardContent class="pt-6 space-y-2">
              <Button type="submit" class="w-full" :disabled="form.processing">
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ form.processing ? t('common.saving', 'Saqlanmoqda...') : t('common.update', 'Yangilash') }}
              </Button>

              <Button type="button" variant="outline" class="w-full" as-child>
                <Link :href="route('admin.customers.index')">{{ t('common.cancel', 'Bekor qilish') }}</Link>
              </Button>

              <Separator />

              <AlertDialog>
                <AlertDialogTrigger as-child>
                  <Button type="button" variant="destructive" class="w-full">
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
    </form>
  </div>
</template>
