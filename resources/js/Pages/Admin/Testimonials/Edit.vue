<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
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
  testimonial: any;
}>();

const activeTab = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  rating: props.testimonial.rating,
  sort_order: props.testimonial.sort_order,
  status: props.testimonial.status,
  uz: { client_name: props.testimonial.uz?.client_name || '', client_role: props.testimonial.uz?.client_role || '', comment: props.testimonial.uz?.comment || '' },
  ru: { client_name: props.testimonial.ru?.client_name || '', client_role: props.testimonial.ru?.client_role || '', comment: props.testimonial.ru?.comment || '' },
  en: { client_name: props.testimonial.en?.client_name || '', client_role: props.testimonial.en?.client_role || '', comment: props.testimonial.en?.comment || '' },
});

const submit = () => {
  form.put(route('admin.testimonials.update', props.testimonial.id));
};

const deleteTestimonial = () => {
  router.delete(route('admin.testimonials.destroy', props.testimonial.id));
};

const hasTranslationErrors = (locale: string) => {
  return form.errors[`${locale}.client_name`] || form.errors[`${locale}.comment`];
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('uz-UZ');
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('common.edit', 'Tahrirlash') }}</h1>
        <p class="text-muted-foreground">{{ testimonial.uz?.client_name || testimonial.ru?.client_name || 'Sharh' }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.testimonials.index')">{{ t('common.back', 'Orqaga') }}</Link>
      </Button>
    </div>

    <form @submit.prevent="submit">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Translations -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>
                </svg>
                {{ t('translations.title', 'Tarjimalar') }}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex gap-2 mb-4">
                <Button
                  v-for="tab in languageTabs"
                  :key="tab.key"
                  type="button"
                  :variant="activeTab === tab.key ? 'default' : 'outline'"
                  size="sm"
                  @click="activeTab = tab.key"
                  class="relative"
                >
                  {{ tab.flag }} {{ tab.label }}
                  <span v-if="hasTranslationErrors(tab.key)" class="absolute -top-1 -right-1 w-2 h-2 bg-destructive rounded-full"></span>
                </Button>
              </div>

              <div v-show="activeTab === 'uz'" class="space-y-4">
                <div class="space-y-2">
                  <Label>{{ t('testimonials.clientName', 'Mijoz ismi') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.uz.client_name" placeholder="Ism Familiya" />
                  <p v-if="form.errors['uz.client_name']" class="text-destructive text-xs">{{ form.errors['uz.client_name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>{{ t('testimonials.clientRole', 'Lavozimi/Kasbi') }}</Label>
                  <Input v-model="form.uz.client_role" placeholder="Masalan: Uy bekasi" />
                </div>
                <div class="space-y-2">
                  <Label>{{ t('testimonials.comment', 'Sharh') }} <span class="text-destructive">*</span></Label>
                  <Textarea v-model="form.uz.comment" rows="5" placeholder="Mijoz sharhi..." />
                  <p v-if="form.errors['uz.comment']" class="text-destructive text-xs">{{ form.errors['uz.comment'] }}</p>
                </div>
              </div>

              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div class="space-y-2">
                  <Label>Имя клиента <span class="text-destructive">*</span></Label>
                  <Input v-model="form.ru.client_name" placeholder="Имя Фамилия" />
                  <p v-if="form.errors['ru.client_name']" class="text-destructive text-xs">{{ form.errors['ru.client_name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Должность/Профессия</Label>
                  <Input v-model="form.ru.client_role" placeholder="Например: Домохозяйка" />
                </div>
                <div class="space-y-2">
                  <Label>Отзыв <span class="text-destructive">*</span></Label>
                  <Textarea v-model="form.ru.comment" rows="5" placeholder="Отзыв клиента..." />
                  <p v-if="form.errors['ru.comment']" class="text-destructive text-xs">{{ form.errors['ru.comment'] }}</p>
                </div>
              </div>

              <div v-show="activeTab === 'en'" class="space-y-4">
                <div class="space-y-2">
                  <Label>Client Name <span class="text-destructive">*</span></Label>
                  <Input v-model="form.en.client_name" placeholder="Full Name" />
                  <p v-if="form.errors['en.client_name']" class="text-destructive text-xs">{{ form.errors['en.client_name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Role/Profession</Label>
                  <Input v-model="form.en.client_role" placeholder="e.g., Homemaker" />
                </div>
                <div class="space-y-2">
                  <Label>Testimonial <span class="text-destructive">*</span></Label>
                  <Textarea v-model="form.en.comment" rows="5" placeholder="Client testimonial..." />
                  <p v-if="form.errors['en.comment']" class="text-destructive text-xs">{{ form.errors['en.comment'] }}</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Rating -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('testimonials.rating', 'Baho') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center gap-1">
                <button
                  v-for="s in 5"
                  :key="s"
                  type="button"
                  @click="form.rating = s"
                  class="text-2xl transition hover:scale-110"
                  :class="s <= form.rating ? 'text-yellow-400' : 'text-muted'"
                >
                  ★
                </button>
              </div>
              <p class="text-xs text-muted-foreground mt-2">{{ t('testimonials.ratingHint', 'Mijoz bergan baho') }}</p>
            </CardContent>
          </Card>

          <!-- Sort Order -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.sortOrder', 'Tartib raqami') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <Input type="number" v-model.number="form.sort_order" min="0" />
            </CardContent>
          </Card>

          <!-- Status -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.status', 'Status') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-between">
                <div>
                  <Label>{{ t('common.active', 'Faol') }}</Label>
                  <p class="text-xs text-muted-foreground">{{ form.status ? 'Saytda ko\'rinadi' : 'Yashirin' }}</p>
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
                <span class="font-mono">#{{ testimonial.id }}</span>
              </div>
              <Separator />
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.createdAt', 'Yaratilgan') }}:</span>
                <span>{{ formatDate(testimonial.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.updatedAt', 'Yangilangan') }}:</span>
                <span>{{ formatDate(testimonial.updated_at) }}</span>
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
                <Link :href="route('admin.testimonials.index')">{{ t('common.cancel', 'Bekor qilish') }}</Link>
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
                      {{ t('testimonials.confirmDelete', 'Bu sharhni o\'chirishni xohlaysizmi?') }}
                      <strong class="block mt-2">{{ testimonial.uz?.client_name }}</strong>
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>{{ t('common.cancel', 'Bekor') }}</AlertDialogCancel>
                    <AlertDialogAction @click="deleteTestimonial" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
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
