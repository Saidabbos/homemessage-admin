<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const form = useForm({
  name: '',
  email: '',
  phone: '',
  password: '',
  status: true,
});

const submit = () => {
  form.post(route('admin.dispatchers.store'));
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">{{ t('dispatchers.new', 'Yangi dispetcher') }}</h1>
      <p class="text-muted-foreground">{{ t('dispatchers.createSubtitle', 'Yangi dispetcher qo\'shish') }}</p>
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
                {{ t('dispatchers.personalInfo', 'Shaxsiy ma\'lumotlar') }}
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <Label>{{ t('dispatchers.name', 'Ism') }} <span class="text-destructive">*</span></Label>
                <Input v-model="form.name" placeholder="Ism Familiya" />
                <p v-if="form.errors.name" class="text-destructive text-xs">{{ form.errors.name }}</p>
              </div>

              <div class="space-y-2">
                <Label>{{ t('dispatchers.email', 'Email') }} <span class="text-destructive">*</span></Label>
                <Input type="email" v-model="form.email" placeholder="email@example.com" />
                <p v-if="form.errors.email" class="text-destructive text-xs">{{ form.errors.email }}</p>
              </div>

              <div class="space-y-2">
                <Label>{{ t('dispatchers.phone', 'Telefon') }}</Label>
                <Input v-model="form.phone" placeholder="+998 90 123 45 67" />
                <p v-if="form.errors.phone" class="text-destructive text-xs">{{ form.errors.phone }}</p>
              </div>

              <div class="space-y-2">
                <Label>{{ t('dispatchers.password', 'Parol') }} <span class="text-destructive">*</span></Label>
                <Input type="password" v-model="form.password" placeholder="••••••••" />
                <p v-if="form.errors.password" class="text-destructive text-xs">{{ form.errors.password }}</p>
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
                  <p class="text-xs text-muted-foreground">{{ form.status ? 'Faol' : 'Nofaol' }}</p>
                </div>
                <Switch v-model:checked="form.status" />
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
                {{ form.processing ? t('common.saving', 'Saqlanmoqda...') : t('common.save', 'Saqlash') }}
              </Button>
              <Button type="button" variant="outline" class="w-full" as-child>
                <Link href="/admin/dispatchers">{{ t('common.cancel', 'Bekor qilish') }}</Link>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </form>
  </div>
</template>
