<script setup lang="ts">
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Checkbox } from '@/components/ui/checkbox';
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
  serviceType: any;
}>();

const activeTab = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const initialDurations = props.serviceType.durations?.length > 0
  ? props.serviceType.durations.map((d: any) => ({ ...d }))
  : [{ id: null, duration: 60, price: 100000, is_default: true, status: true }];

const form = useForm({
  slug: props.serviceType.slug,
  image: null as File | null,
  status: props.serviceType.status,
  en: { name: props.serviceType.en?.name || '', description: props.serviceType.en?.description || '' },
  uz: { name: props.serviceType.uz?.name || '', description: props.serviceType.uz?.description || '' },
  ru: { name: props.serviceType.ru?.name || '', description: props.serviceType.ru?.description || '' },
  durations: initialDurations,
});

const addDuration = () => {
  form.durations.push({ id: null, duration: 60, price: 100000, is_default: false, status: true });
};

const removeDuration = (index: number) => {
  if (form.durations.length > 1) {
    const wasDefault = form.durations[index].is_default;
    form.durations.splice(index, 1);
    if (wasDefault && form.durations.length > 0) {
      form.durations[0].is_default = true;
    }
  }
};

const setDefault = (index: number) => {
  form.durations.forEach((d: any, i: number) => { d.is_default = i === index; });
};

const submit = () => {
  form.patch(route('admin.service-types.update', props.serviceType.id), { preserveScroll: true });
};

const deleteServiceType = () => {
  router.delete(route('admin.service-types.destroy', props.serviceType.id));
};

const hasTranslationErrors = (locale: string) => {
  return form.errors[`${locale}.name`] || form.errors[`${locale}.description`];
};

const formatDate = (date: string) => new Date(date).toLocaleDateString('uz-UZ');
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ t('common.edit', 'Tahrirlash') }}</h1>
        <p class="text-muted-foreground">{{ serviceType.uz?.name || serviceType.name }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.service-types.index')">{{ t('common.back', 'Orqaga') }}</Link>
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
                  <Label>Nomi (O'zbek) <span class="text-destructive">*</span></Label>
                  <Input v-model="form.uz.name" placeholder="Masalan: Klassik massaj" />
                  <p v-if="form.errors['uz.name']" class="text-destructive text-xs">{{ form.errors['uz.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Tavsifi (O'zbek)</Label>
                  <Textarea v-model="form.uz.description" rows="4" placeholder="Xizmat haqida batafsil..." />
                </div>
              </div>

              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div class="space-y-2">
                  <Label>Название (Русский) <span class="text-destructive">*</span></Label>
                  <Input v-model="form.ru.name" placeholder="Например: Классический массаж" />
                  <p v-if="form.errors['ru.name']" class="text-destructive text-xs">{{ form.errors['ru.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Описание (Русский)</Label>
                  <Textarea v-model="form.ru.description" rows="4" placeholder="Подробное описание..." />
                </div>
              </div>

              <div v-show="activeTab === 'en'" class="space-y-4">
                <div class="space-y-2">
                  <Label>Name (English) <span class="text-destructive">*</span></Label>
                  <Input v-model="form.en.name" placeholder="e.g., Classic Massage" />
                  <p v-if="form.errors['en.name']" class="text-destructive text-xs">{{ form.errors['en.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Description (English)</Label>
                  <Textarea v-model="form.en.description" rows="4" placeholder="Detailed description..." />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Durations -->
          <Card>
            <CardHeader class="flex flex-row items-center justify-between">
              <CardTitle class="flex items-center gap-2">
                <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ t('serviceTypes.durations', 'Davomiylik va Narxlar') }}
              </CardTitle>
              <Button type="button" size="sm" @click="addDuration">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Qo'shish
              </Button>
            </CardHeader>
            <CardContent>
              <p v-if="form.errors.durations" class="text-destructive text-sm mb-3">{{ form.errors.durations }}</p>

              <div class="space-y-3">
                <div
                  v-for="(duration, index) in form.durations"
                  :key="index"
                  class="flex items-center gap-3 p-3 rounded-lg border"
                  :class="duration.is_default ? 'border-primary bg-primary/5' : ''"
                >
                  <div class="flex-1 space-y-1">
                    <Label class="text-xs text-muted-foreground">Davomiylik (daq)</Label>
                    <Input type="number" v-model.number="duration.duration" min="15" max="480" step="15" />
                  </div>

                  <div class="flex-1 space-y-1">
                    <Label class="text-xs text-muted-foreground">Narx (so'm)</Label>
                    <Input type="number" v-model.number="duration.price" min="0" step="1000" />
                  </div>

                  <div class="flex flex-col items-center gap-1">
                    <Label class="text-xs text-muted-foreground">Asosiy</Label>
                    <input type="radio" :checked="duration.is_default" @change="setDefault(index)" class="w-4 h-4" />
                  </div>

                  <div class="flex flex-col items-center gap-1">
                    <Label class="text-xs text-muted-foreground">Faol</Label>
                    <Checkbox :checked="duration.status" @update:checked="duration.status = $event" />
                  </div>

                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    @click="removeDuration(index)"
                    :disabled="form.durations.length <= 1"
                    class="text-destructive hover:text-destructive"
                  >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </Button>
                </div>
              </div>

              <p class="mt-3 text-xs text-muted-foreground">
                Asosiy davomiylik - booking wizardda default tanlanadi.
              </p>
            </CardContent>
          </Card>

          <!-- Slug -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.slug', 'Slug') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <Label>Slug (URL)</Label>
                <Input v-model="form.slug" class="font-mono" placeholder="klassik-massaj" />
                <p v-if="form.errors.slug" class="text-destructive text-xs">{{ form.errors.slug }}</p>
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

          <!-- Image -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.image', 'Rasm') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="serviceType.image_url" class="mb-4">
                <p class="text-xs text-muted-foreground mb-2">Joriy rasm:</p>
                <img :src="serviceType.image_url" :alt="serviceType.name" class="w-full rounded-lg border" />
              </div>
              <ImageUpload v-model="form.image" :error="form.errors.image" :current-image="serviceType.image_url" />
              <p class="mt-2 text-xs text-muted-foreground">Yangi rasm tanlash ixtiyoriy</p>
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
                <span class="font-mono">#{{ serviceType.id }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Davomiyliklar:</span>
                <span>{{ form.durations.length }} ta</span>
              </div>
              <Separator />
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.createdAt', 'Yaratilgan') }}:</span>
                <span>{{ formatDate(serviceType.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.updatedAt', 'Yangilangan') }}:</span>
                <span>{{ formatDate(serviceType.updated_at) }}</span>
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
                <Link :href="route('admin.service-types.index')">{{ t('common.cancel', 'Bekor qilish') }}</Link>
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
                      {{ t('serviceTypes.confirmDelete', 'Bu xizmat turini o\'chirishni xohlaysizmi?') }}
                      <strong class="block mt-2">{{ serviceType.uz?.name }}</strong>
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>{{ t('common.cancel', 'Bekor') }}</AlertDialogCancel>
                    <AlertDialogAction @click="deleteServiceType" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
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
