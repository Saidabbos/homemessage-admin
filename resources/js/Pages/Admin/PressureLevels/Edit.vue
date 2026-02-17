<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm, Link, router } from '@inertiajs/vue3';
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
  pressureLevel: any;
}>();

const activeTab = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  slug: props.pressureLevel.slug,
  status: props.pressureLevel.status,
  en: { name: props.pressureLevel.en?.name || '', description: props.pressureLevel.en?.description || '' },
  uz: { name: props.pressureLevel.uz?.name || '', description: props.pressureLevel.uz?.description || '' },
  ru: { name: props.pressureLevel.ru?.name || '', description: props.pressureLevel.ru?.description || '' },
  sort_order: props.pressureLevel.sort_order,
});

const submit = () => {
  form.patch(route('admin.pressure-levels.update', props.pressureLevel.id), { preserveScroll: true });
};

const deletePressureLevel = () => {
  router.delete(route('admin.pressure-levels.destroy', props.pressureLevel.id));
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
        <p class="text-muted-foreground">{{ pressureLevel.uz?.name || pressureLevel.en?.name }}</p>
      </div>
      <Button variant="outline" as-child>
        <Link :href="route('admin.pressure-levels.index')">{{ t('common.back', 'Orqaga') }}</Link>
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
                  <Input v-model="form.uz.name" placeholder="Masalan: Yumshoq" />
                  <p v-if="form.errors['uz.name']" class="text-destructive text-xs">{{ form.errors['uz.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Tavsifi (O'zbek)</Label>
                  <Textarea v-model="form.uz.description" rows="4" placeholder="Bosim darajasi haqida..." />
                </div>
              </div>

              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div class="space-y-2">
                  <Label>Название (Русский) <span class="text-destructive">*</span></Label>
                  <Input v-model="form.ru.name" placeholder="Например: Мягкое давление" />
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
                  <Input v-model="form.en.name" placeholder="e.g., Light Pressure" />
                  <p v-if="form.errors['en.name']" class="text-destructive text-xs">{{ form.errors['en.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>Description (English)</Label>
                  <Textarea v-model="form.en.description" rows="4" placeholder="Detailed description..." />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Basic Info -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.basicInfo', 'Asosiy ma\'lumotlar') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="space-y-2">
                <Label>Slug (URL)</Label>
                <Input v-model="form.slug" class="font-mono" />
                <p v-if="form.errors.slug" class="text-destructive text-xs">{{ form.errors.slug }}</p>
              </div>
              <div class="space-y-2">
                <Label>{{ t('common.sortOrder', 'Tartib raqami') }}</Label>
                <Input type="number" v-model.number="form.sort_order" min="0" />
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

          <!-- Meta Info -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.info', 'Ma\'lumot') }}</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-muted-foreground">ID:</span>
                <span class="font-mono">#{{ pressureLevel.id }}</span>
              </div>
              <Separator />
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.createdAt', 'Yaratilgan') }}:</span>
                <span>{{ formatDate(pressureLevel.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">{{ t('common.updatedAt', 'Yangilangan') }}:</span>
                <span>{{ formatDate(pressureLevel.updated_at) }}</span>
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
                <Link :href="route('admin.pressure-levels.index')">{{ t('common.cancel', 'Bekor qilish') }}</Link>
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
                      {{ t('pressureLevels.confirmDelete', 'Bu bosim darajasini o\'chirishni xohlaysizmi?') }}
                      <strong class="block mt-2">{{ pressureLevel.uz?.name }}</strong>
                    </AlertDialogDescription>
                  </AlertDialogHeader>
                  <AlertDialogFooter>
                    <AlertDialogCancel>{{ t('common.cancel', 'Bekor') }}</AlertDialogCancel>
                    <AlertDialogAction @click="deletePressureLevel" class="bg-destructive text-destructive-foreground hover:bg-destructive/90">
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
