<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import ImageUpload from '@/Components/Admin/ImageUpload.vue';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

const props = defineProps<{
  oil: {
    id: number;
    slug: string;
    status: boolean;
    image_url?: string;
    uz?: { name: string; description: string };
    ru?: { name: string; description: string };
    en?: { name: string; description: string };
  };
}>();

const activeTab = ref('uz');

const languageTabs = [
  { key: 'uz', label: "O'zbek", flag: '🇺🇿' },
  { key: 'ru', label: 'Русский', flag: '🇷🇺' },
  { key: 'en', label: 'English', flag: '🇬🇧' },
];

const form = useForm({
  _method: 'PUT',
  slug: props.oil.slug,
  image: null as File | null,
  status: props.oil.status,
  uz: { name: props.oil.uz?.name || '', description: props.oil.uz?.description || '' },
  ru: { name: props.oil.ru?.name || '', description: props.oil.ru?.description || '' },
  en: { name: props.oil.en?.name || '', description: props.oil.en?.description || '' },
});

const submit = () => {
  form.patch(route('admin.oils.update', props.oil.id));
};

const deleteOil = () => {
  if (confirm(t('oils.confirmDelete', 'Haqiqatan o\'chirmoqchimisiz?'))) {
    router.delete(route('admin.oils.destroy', props.oil.id));
  }
};
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-bold tracking-tight">{{ t('oils.edit', 'Yog\'ni tahrirlash') }}</h1>
      <p class="text-muted-foreground">{{ oil.uz?.name || oil.slug }}</p>
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
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
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
                >
                  {{ tab.flag }} {{ tab.label }}
                </Button>
              </div>

              <div v-show="activeTab === 'uz'" class="space-y-4">
                <div class="space-y-2">
                  <Label>{{ t('translations.name', 'Nomi') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.uz.name" />
                  <p v-if="form.errors['uz.name']" class="text-destructive text-xs">{{ form.errors['uz.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>{{ t('translations.description', 'Tavsifi') }}</Label>
                  <Textarea v-model="form.uz.description" rows="4" />
                </div>
              </div>

              <div v-show="activeTab === 'ru'" class="space-y-4">
                <div class="space-y-2">
                  <Label>{{ t('translations.name', 'Название') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.ru.name" />
                  <p v-if="form.errors['ru.name']" class="text-destructive text-xs">{{ form.errors['ru.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>{{ t('translations.description', 'Описание') }}</Label>
                  <Textarea v-model="form.ru.description" rows="4" />
                </div>
              </div>

              <div v-show="activeTab === 'en'" class="space-y-4">
                <div class="space-y-2">
                  <Label>{{ t('translations.name', 'Name') }} <span class="text-destructive">*</span></Label>
                  <Input v-model="form.en.name" />
                  <p v-if="form.errors['en.name']" class="text-destructive text-xs">{{ form.errors['en.name'] }}</p>
                </div>
                <div class="space-y-2">
                  <Label>{{ t('translations.description', 'Description') }}</Label>
                  <Textarea v-model="form.en.description" rows="4" />
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Slug -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.slug', 'Slug') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-2">
                <Label>{{ t('common.slug', 'Slug') }} <span class="text-destructive">*</span></Label>
                <Input v-model="form.slug" class="font-mono" />
                <p v-if="form.errors.slug" class="text-destructive text-xs">{{ form.errors.slug }}</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Current Image -->
          <Card v-if="oil.image_url">
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.currentImage', 'Joriy rasm') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <img :src="oil.image_url" :alt="oil.slug" class="w-full rounded border" />
            </CardContent>
          </Card>

          <!-- New Image -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.newImage', 'Yangi rasm') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <ImageUpload v-model="form.image" />
              <p v-if="form.errors.image" class="text-destructive text-xs mt-2">{{ form.errors.image }}</p>
            </CardContent>
          </Card>

          <!-- Status -->
          <Card>
            <CardHeader>
              <CardTitle class="text-base">{{ t('common.status', 'Status') }}</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="flex items-center justify-between">
                <Label>{{ t('common.active', 'Faol') }}</Label>
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
                {{ form.processing ? t('common.saving', 'Saqlanmoqda...') : t('common.update', 'Yangilash') }}
              </Button>
              <Button type="button" variant="outline" class="w-full" as-child>
                <Link href="/admin/oils">{{ t('common.back', 'Orqaga') }}</Link>
              </Button>
              <Button type="button" variant="destructive" class="w-full" @click="deleteOil">
                {{ t('common.delete', 'O\'chirish') }}
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </form>
  </div>
</template>
